<?php
namespace App\Controllers;

use App\Models\KriteriaModel;
use App\Models\PresetModel;

class Ahp extends BaseController
{
    protected $kriteriaModel;
    protected $presetModel;

    // Nilai Random Index (RI) berdasarkan jumlah kriteria (n)
    // Sumber: Tabel 2.2 Skripsi (Saaty, 1980)
    // Index 0 kosong, index 1=0, index 5=1.12
    protected $ir_table = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49
    ];

    public function __construct()
    {
        $this->kriteriaModel = new KriteriaModel();
        $this->presetModel = new PresetModel();
    }

    public function index()
    {
        $kriteria = $this->kriteriaModel->findAll();
        $data = [
            'title' => 'Pembobotan AHP',
            'kriteria' => $kriteria,
            'presets' => $this->getPresets($kriteria)
        ];
        return view('ahp/index', $data);
    }

    // Data Preset Skenario Pembobotan
    private function getPresets(array $kriteria)
    {
        $presets = [
            [
                'id' => 'netral',
                'nama' => 'Netral (Sama Penting)',
                'deskripsi' => 'Semua kriteria dianggap memiliki kepentingan yang sama.',
                'type' => 'logic', // Tipe Logic: Menggunakan Index urutan
                'data' => [] // Kosong = Default nilai 1
            ],
            [
                'id' => 'akademik',
                'nama' => 'Fokus Akademik (C1/C6 Dominan)',
                'deskripsi' => 'C1 (non-inti) dan C6 (inti) diprioritaskan lebih tinggi dibanding kriteria lain.',
                'type' => 'logic',
                'data' => $this->buildAkademikPresetData($kriteria),
            ],
            [
                'id' => 'karakter',
                'nama' => 'Fokus Karakter/Sikap (C3 Dominan)',
                'deskripsi' => 'C3 dianggap lebih penting dibanding kriteria lain, termasuk C6.',
                'type' => 'logic',
                'data' => $this->buildKarakterPresetData($kriteria),
            ]
        ];

        // Ambil Preset User dari Database
        $userPresets = $this->presetModel->findAll();
        foreach ($userPresets as $p) {
            $presets[] = [
                'id' => $p['id_preset'], // ID Database (Angka)
                'nama' => $p['nama_preset'] . ' (User)',
                'deskripsi' => $p['deskripsi'],
                'type' => 'raw', // Tipe Raw: Data mentah form (nilai_x_y)
                'data' => json_decode($p['data_json'], true)
            ];
        }

        return $presets;
    }

    private function buildAkademikPresetData(array $kriteria): array
    {
        $data = [];
        $indexMap = $this->buildKodeIndexMap($kriteria);
        $priorityAcademic = [];

        if (isset($indexMap['C1'])) {
            $priorityAcademic[] = 'C1';
        }
        if (isset($indexMap['C6'])) {
            $priorityAcademic[] = 'C6';
        }

        foreach ($priorityAcademic as $kode) {
            $idxAcademic = $indexMap[$kode];
            foreach ($indexMap as $otherKode => $otherIdx) {
                if ($otherKode === $kode) {
                    continue;
                }

                $scale = 5;
                if ($otherKode === 'C2') {
                    $scale = 3;
                }
                if (in_array($otherKode, $priorityAcademic, true)) {
                    $scale = 2;
                }

                $this->setLogicComparison($data, $idxAcademic, $otherIdx, $scale, $idxAcademic);
            }
        }

        return $data;
    }

    private function buildKarakterPresetData(array $kriteria): array
    {
        $data = [];
        $indexMap = $this->buildKodeIndexMap($kriteria);
        if (!isset($indexMap['C3'])) {
            return $data;
        }

        $idxC3 = $indexMap['C3'];
        foreach ($indexMap as $kode => $idx) {
            if ($kode === 'C3') {
                continue;
            }

            $scale = in_array($kode, ['C4', 'C5'], true) ? 5 : 3;
            $this->setLogicComparison($data, $idxC3, $idx, $scale, $idxC3);
        }

        return $data;
    }

    private function buildKodeIndexMap(array $kriteria): array
    {
        $map = [];
        foreach (array_values($kriteria) as $idx => $k) {
            $kode = strtoupper((string) ($k['kode_kriteria'] ?? ''));
            if ($kode !== '') {
                $map[$kode] = $idx;
            }
        }
        return $map;
    }

    private function setLogicComparison(array &$data, int $idxA, int $idxB, int $scale, int $winnerIdx): void
    {
        if ($idxA === $idxB) {
            return;
        }

        $left = min($idxA, $idxB);
        $right = max($idxA, $idxB);
        $winner = ($winnerIdx === $left) ? 0 : 1;
        $key = $left . '|' . $right;

        $data[$key] = [$scale, $winner];
    }

    public function simpanPreset()
    {
        $nama_preset = $this->request->getPost('nama_preset');

        // Cek Duplikasi Nama
        $existing = $this->presetModel->where('nama_preset', $nama_preset)->first();
        if ($existing) {
            return redirect()->to('/ahp')->with('error', 'Gagal menyimpan! Nama preset "' . $nama_preset . '" sudah ada.');
        }

        $this->presetModel->save([
            'nama_preset' => $nama_preset,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'data_json' => $this->request->getPost('data_json') // JSON string dari JS
        ]);
        return redirect()->to('/ahp')->with('success', 'Preset baru berhasil disimpan!');
    }

    public function hapusPreset($id)
    {
        // Pastikan hanya menghapus preset user (ID angka)
        $this->presetModel->delete($id);
        return redirect()->to('/ahp')->with('success', 'Preset berhasil dihapus.');
    }

    public function proses()
    {
        $kriteria = $this->kriteriaModel->findAll();
        $n = count($kriteria); // Jumlah kriteria aktif (dinamis)
        $input = $this->request->getPost();

        // 1. Inisialisasi Matriks Perbandingan (Isi diagonal dengan 1)
        $matriks = [];
        foreach ($kriteria as $k1) {
            foreach ($kriteria as $k2) {
                if ($k1['id_kriteria'] == $k2['id_kriteria']) {
                    $matriks[$k1['id_kriteria']][$k2['id_kriteria']] = 1;
                }
            }
        }

        // 2. Isi Matriks dari Input User
        // Loop untuk menangkap input perbandingan
        foreach ($kriteria as $i => $k1) {
            foreach ($kriteria as $j => $k2) {
                if ($j > $i) { // Hanya proses segitiga atas matriks
                    $id1 = $k1['id_kriteria'];
                    $id2 = $k2['id_kriteria'];

                    // Ambil nilai dari form
                    $nilai = $input['nilai_' . $id1 . '_' . $id2];
                    $dominan = $input['pilih_' . $id1 . '_' . $id2]; // Siapa yang lebih penting?

                    if ($dominan == $id1) {
                        // Jika K1 lebih penting dari K2
                        $matriks[$id1][$id2] = $nilai;
                        $matriks[$id2][$id1] = 1 / $nilai;
                    } else {
                        // Jika K2 lebih penting dari K1
                        $matriks[$id1][$id2] = 1 / $nilai;
                        $matriks[$id2][$id1] = $nilai;
                    }
                }
            }
        }

        // 3. Menghitung Total Per Kolom
        $total_kolom = [];
        foreach ($kriteria as $k) {
            $sum = 0;
            foreach ($kriteria as $row) {
                $sum += $matriks[$row['id_kriteria']][$k['id_kriteria']];
            }
            $total_kolom[$k['id_kriteria']] = $sum;
        }

        // 4. Normalisasi Matriks & Hitung Bobot Prioritas (Eigen Vector)
        $bobot_prioritas = [];
        foreach ($kriteria as $row) {
            $sum_baris = 0;
            foreach ($kriteria as $col) {
                // Nilai sel dibagi total kolomnya
                $norm = $matriks[$row['id_kriteria']][$col['id_kriteria']] / $total_kolom[$col['id_kriteria']];
                $sum_baris += $norm;
            }
            // Rata-rata baris = Bobot
            $bobot_prioritas[$row['id_kriteria']] = $sum_baris / $n;
        }

        // 5. Uji Konsistensi (Consistency Ratio / CR)
        // Hitung Lambda Max (Eigenvalue Max)
        $lambda_max = 0;
        foreach ($kriteria as $k) {
            $lambda_max += ($total_kolom[$k['id_kriteria']] * $bobot_prioritas[$k['id_kriteria']]);
        }

        // Hitung CI (Consistency Index) -> Rumus 2.7 Skripsi
        $CI = ($lambda_max - $n) / ($n - 1);

        // Ambil RI (Random Index) -> Tabel 2.2 Skripsi
        $RI = $this->ir_table[$n] ?? 1.12;

        // Hitung CR (Consistency Ratio) -> Rumus 2.8 Skripsi
        $CR = ($RI != 0) ? $CI / $RI : 0;

        // 6. Simpan ke Database JIKA Konsisten (CR <= 0.1)
        $pesan_konsistensi = "";
        $is_consistent = false;

        if ($CR <= 0.1) {
            $is_consistent = true;
            $pesan_konsistensi = "Nilai CR " . number_format($CR, 4) . " (Konsisten). Bobot berhasil disimpan!";

            // Update Bobot ke Tabel Kriteria
            foreach ($bobot_prioritas as $id_kriteria => $bobot) {
                $this->kriteriaModel->update($id_kriteria, ['bobot' => $bobot]);
            }
        } else {
            $pesan_konsistensi = "Nilai CR " . number_format($CR, 4) . " (TIDAK KONSISTEN). Silakan ulangi penilaian!";
        }

        // Siapkan Data untuk Grafik Pie Chart
        $chart_labels = [];
        $chart_data = [];
        foreach ($kriteria as $k) {
            $chart_labels[] = $k['kode_kriteria'] . ' - ' . kriteria_label($k['nama_kriteria']);
            $chart_data[] = round($bobot_prioritas[$k['id_kriteria']] * 100, 2); // Dalam Persen
        }

        // Kirim data ke View Hasil
        $data = [
            'title' => 'Hasil Perhitungan AHP',
            'matriks' => $matriks,
            'bobot' => $bobot_prioritas,
            'CR' => $CR,
            'is_consistent' => $is_consistent,
            'pesan' => $pesan_konsistensi,
            'kriteria' => $kriteria,
            'chart_labels' => json_encode($chart_labels),
            'chart_data' => json_encode($chart_data)
        ];

        return view('ahp/hasil', $data);
    }
}
