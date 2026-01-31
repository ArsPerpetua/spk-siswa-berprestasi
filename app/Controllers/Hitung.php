<?php
namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;

class Hitung extends BaseController
{
    protected $alternatifModel;
    protected $kriteriaModel;
    protected $penilaianModel;

    public function __construct()
    {
        $this->alternatifModel = new AlternatifModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->penilaianModel = new PenilaianModel();
    }

    public function index()
    {
        // 1. Ambil Data
        $data['kriteria'] = $this->kriteriaModel->findAll();

        // Cek apakah bobot sudah dihitung dan valid (Total mendekati 1)
        $total_bobot = array_sum(array_column($data['kriteria'], 'bobot'));
        if ($total_bobot < 0.99 || $total_bobot > 1.01) {
            return redirect()->to('/ahp')->with('error', "Bobot kriteria belum konsisten atau belum dihitung (Total: " . number_format($total_bobot, 4) . "). Silakan lakukan pembobotan AHP terlebih dahulu.");
        }

        $data['alternatif'] = $this->alternatifModel->findAll();
        $penilaian = $this->penilaianModel->findAll();

        if (empty($data['alternatif']) || empty($penilaian)) {
            return view('hasil_perhitungan', array_merge($data, [
                'matriks' => [],
                'hasil_moora' => [],
                'hasil_aras' => [],
                'error_msg' => 'Data Siswa atau Penilaian masih kosong.'
            ]));
        }

        // --- 1. MATRIKS KEPUTUSAN (X) ---
        $matriks = [];
        foreach ($penilaian as $row) {
            $matriks[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
        }
        $data['matriks'] = $matriks;

        // ==========================================
        // DETAIL PERHITUNGAN MOORA
        // ==========================================

        // A. Pembagi (Akar Kuadrat)
        $moora_pembagi = [];
        foreach ($data['kriteria'] as $k) {
            $sum_kuadrat = 0;
            foreach ($data['alternatif'] as $a) {
                $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                $sum_kuadrat += pow($val, 2);
            }
            $moora_pembagi[$k['id_kriteria']] = sqrt($sum_kuadrat);
        }
        $data['moora_pembagi'] = $moora_pembagi; // Kirim ke View

        // B. Normalisasi MOORA (X*)
        $moora_normalisasi = [];
        foreach ($data['alternatif'] as $a) {
            foreach ($data['kriteria'] as $k) {
                $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                $pembagi = $moora_pembagi[$k['id_kriteria']];
                $moora_normalisasi[$a['id_alternatif']][$k['id_kriteria']] = ($pembagi != 0) ? $val / $pembagi : 0;
            }
        }
        $data['moora_normalisasi'] = $moora_normalisasi; // Kirim ke View

        // C. Nilai Optimasi (Yi)
        $hasil_moora = [];
        foreach ($data['alternatif'] as $a) {
            $total_benefit = 0;
            $total_cost = 0;
            foreach ($data['kriteria'] as $k) {
                $norm = $moora_normalisasi[$a['id_alternatif']][$k['id_kriteria']];
                $weighted = $norm * $k['bobot'];

                if ($k['jenis'] == 'benefit') {
                    $total_benefit += $weighted;
                } else {
                    $total_cost += $weighted;
                }
            }
            $hasil_moora[] = [
                'nis' => $a['nis'],
                'nama' => $a['nama_siswa'],
                'kelas' => $a['kelas'], // Pastikan ini ada
                'max' => $total_benefit,
                'min' => $total_cost,
                'nilai' => $total_benefit - $total_cost
            ];
        }
        usort($hasil_moora, function ($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });
        $data['hasil_moora'] = $hasil_moora;


        // ==========================================
        // DETAIL PERHITUNGAN ARAS
        // ==========================================

        // A. Menentukan A0 (Ideal)
        $A0 = [];
        foreach ($data['kriteria'] as $k) {
            $col_values = [];
            foreach ($data['alternatif'] as $a) {
                $col_values[] = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
            }
            if ($k['jenis'] == 'benefit') {
                $A0[$k['id_kriteria']] = !empty($col_values) ? max($col_values) : 0;
            } else {
                $A0[$k['id_kriteria']] = !empty($col_values) ? min($col_values) : 0;
            }
        }
        $data['A0'] = $A0; // Kirim ke View

        // Gabung A0 ke data sementara
        $matriks_aras_lengkap = [];
        $matriks_aras_lengkap[0] = $A0; // Index 0 adalah A0
        foreach ($data['alternatif'] as $a) {
            foreach ($data['kriteria'] as $k) {
                $matriks_aras_lengkap[$a['id_alternatif']][$k['id_kriteria']] = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
            }
        }

        // B. Normalisasi ARAS (Rij)
        $total_kolom_aras = [];
        foreach ($data['kriteria'] as $k) {
            $sum = 0;
            foreach ($matriks_aras_lengkap as $id_alt => $vals) {
                $val = $vals[$k['id_kriteria']];
                if ($k['jenis'] == 'cost')
                    $val = ($val != 0) ? 1 / $val : 0;
                $sum += $val;
            }
            $total_kolom_aras[$k['id_kriteria']] = $sum;
        }

        $aras_normalisasi = [];
        foreach ($matriks_aras_lengkap as $id_alt => $vals) {
            foreach ($data['kriteria'] as $k) {
                $val = $vals[$k['id_kriteria']];
                if ($k['jenis'] == 'cost')
                    $val = ($val != 0) ? 1 / $val : 0;

                $pembagi = $total_kolom_aras[$k['id_kriteria']];
                $aras_normalisasi[$id_alt][$k['id_kriteria']] = ($pembagi != 0) ? $val / $pembagi : 0;
            }
        }
        $data['aras_normalisasi'] = $aras_normalisasi; // Kirim ke View

        // C. Matriks Terbobot ARAS (Dij)
        $aras_terbobot = [];
        foreach ($aras_normalisasi as $id_alt => $cols) {
            foreach ($data['kriteria'] as $k) {
                $aras_terbobot[$id_alt][$k['id_kriteria']] = $cols[$k['id_kriteria']] * $k['bobot'];
            }
        }
        $data['aras_terbobot'] = $aras_terbobot; // Kirim ke View

        // D. Hasil Akhir ARAS
        $hasil_aras = [];
        $S0 = array_sum($aras_terbobot[0]);
        $data['S0'] = $S0;

        foreach ($data['alternatif'] as $a) {
            $Si = array_sum($aras_terbobot[$a['id_alternatif']]);
            $Ki = ($S0 != 0) ? $Si / $S0 : 0;

            // Tentukan Predikat Kelayakan (Interpretasi Kualitatif)
            $predikat = "Kurang";
            if ($Ki >= 0.9) $predikat = "Sangat Baik";
            elseif ($Ki >= 0.8) $predikat = "Baik";
            elseif ($Ki >= 0.7) $predikat = "Cukup";
            elseif ($Ki >= 0.6) $predikat = "Kurang";
            else $predikat = "Sangat Kurang";

            $hasil_aras[] = [
                'nis' => $a['nis'],
                'nama' => $a['nama_siswa'],
                'kelas' => $a['kelas'],
                'Si' => $Si,
                'nilai' => $Ki, // Di view sebelumnya kita pakai key 'nilai'
                'predikat' => $predikat
            ];
        }
        usort($hasil_aras, function ($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });
        $data['hasil_aras'] = $hasil_aras;

        return view('hasil_perhitungan', $data);
    }

    public function cetakPDF()
    {
        $data['kriteria'] = $this->kriteriaModel->findAll();

        // Cek Bobot AHP sebelum cetak (Security Check)
        $total_bobot = array_sum(array_column($data['kriteria'], 'bobot'));
        if ($total_bobot < 0.99 || $total_bobot > 1.01) {
            return redirect()->to('/ahp')->with('error', "Bobot kriteria belum valid. Silakan hitung AHP dulu.");
        }

        $data['alternatif'] = $this->alternatifModel->findAll();
        $penilaian = $this->penilaianModel->findAll();

        if (empty($data['alternatif']) || empty($penilaian)) {
            return redirect()->to('/hitung')->with('error', 'Data kosong!');
        }

        // --- 1. MATRIKS KEPUTUSAN (X) ---
        $matriks = [];
        foreach ($penilaian as $row) {
            $matriks[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
        }
        $data['matriks'] = $matriks;

        // ==========================================
        // DETAIL PERHITUNGAN MOORA
        // ==========================================

        // A. Pembagi (Akar Kuadrat)
        $moora_pembagi = [];
        foreach ($data['kriteria'] as $k) {
            $sum_kuadrat = 0;
            foreach ($data['alternatif'] as $a) {
                $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                $sum_kuadrat += pow($val, 2);
            }
            $moora_pembagi[$k['id_kriteria']] = sqrt($sum_kuadrat);
        }
        $data['moora_pembagi'] = $moora_pembagi;

        // B. Normalisasi MOORA (X*)
        $moora_normalisasi = [];
        foreach ($data['alternatif'] as $a) {
            foreach ($data['kriteria'] as $k) {
                $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                $pembagi = $moora_pembagi[$k['id_kriteria']];
                $moora_normalisasi[$a['id_alternatif']][$k['id_kriteria']] = ($pembagi != 0) ? $val / $pembagi : 0;
            }
        }
        $data['moora_normalisasi'] = $moora_normalisasi;

        // C. Nilai Optimasi (Yi)
        $hasil_moora = [];
        foreach ($data['alternatif'] as $a) {
            $total_benefit = 0;
            $total_cost = 0;
            foreach ($data['kriteria'] as $k) {
                $norm = $moora_normalisasi[$a['id_alternatif']][$k['id_kriteria']];
                $weighted = $norm * $k['bobot'];

                if ($k['jenis'] == 'benefit') {
                    $total_benefit += $weighted;
                } else {
                    $total_cost += $weighted;
                }
            }
            $hasil_moora[] = [
                'nis' => $a['nis'],
                'nama' => $a['nama_siswa'],
                'kelas' => $a['kelas'],
                'max' => $total_benefit,
                'min' => $total_cost,
                'nilai' => $total_benefit - $total_cost
            ];
        }
        // Sort Ranking MOORA
        usort($hasil_moora, function ($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });
        $data['hasil_moora'] = $hasil_moora;


        // ==========================================
        // DETAIL PERHITUNGAN ARAS
        // ==========================================

        // A. Menentukan A0 (Ideal)
        $A0 = [];
        foreach ($data['kriteria'] as $k) {
            $col_values = [];
            foreach ($data['alternatif'] as $a) {
                $col_values[] = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
            }
            if ($k['jenis'] == 'benefit') {
                $A0[$k['id_kriteria']] = !empty($col_values) ? max($col_values) : 0;
            } else {
                $A0[$k['id_kriteria']] = !empty($col_values) ? min($col_values) : 0;
            }
        }
        $data['A0'] = $A0;

        // Gabung A0 ke data sementara untuk loop
        $matriks_aras_lengkap = [];
        $matriks_aras_lengkap[0] = $A0; // Index 0 adalah A0
        foreach ($data['alternatif'] as $a) {
            foreach ($data['kriteria'] as $k) {
                $matriks_aras_lengkap[$a['id_alternatif']][$k['id_kriteria']] = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
            }
        }

        // B. Normalisasi ARAS (Rij)
        // Hitung total per kolom dulu (dengan logika Cost 1/x)
        $total_kolom_aras = [];
        foreach ($data['kriteria'] as $k) {
            $sum = 0;
            foreach ($matriks_aras_lengkap as $id_alt => $vals) {
                $val = $vals[$k['id_kriteria']];
                if ($k['jenis'] == 'cost')
                    $val = ($val != 0) ? 1 / $val : 0;
                $sum += $val;
            }
            $total_kolom_aras[$k['id_kriteria']] = $sum;
        }

        $aras_normalisasi = [];
        foreach ($matriks_aras_lengkap as $id_alt => $vals) {
            foreach ($data['kriteria'] as $k) {
                $val = $vals[$k['id_kriteria']];
                if ($k['jenis'] == 'cost')
                    $val = ($val != 0) ? 1 / $val : 0;

                $pembagi = $total_kolom_aras[$k['id_kriteria']];
                $aras_normalisasi[$id_alt][$k['id_kriteria']] = ($pembagi != 0) ? $val / $pembagi : 0;
            }
        }
        $data['aras_normalisasi'] = $aras_normalisasi;

        // C. Matriks Terbobot ARAS (Dij)
        $aras_terbobot = [];
        foreach ($aras_normalisasi as $id_alt => $cols) {
            foreach ($data['kriteria'] as $k) {
                $aras_terbobot[$id_alt][$k['id_kriteria']] = $cols[$k['id_kriteria']] * $k['bobot'];
            }
        }
        $data['aras_terbobot'] = $aras_terbobot;

        // D. Nilai Fungsi Optimalitas (Si) & Ki
        $hasil_aras = [];
        $S0 = array_sum($aras_terbobot[0]); // Nilai Si milik A0

        foreach ($data['alternatif'] as $a) {
            $Si = array_sum($aras_terbobot[$a['id_alternatif']]);
            $Ki = ($S0 != 0) ? $Si / $S0 : 0;

            $hasil_aras[] = [
                'nis' => $a['nis'],
                'nama' => $a['nama_siswa'],
                'kelas' => $a['kelas'],
                'Si' => $Si,
                'Ki' => $Ki
            ];
        }
        // Sort Ranking ARAS
        usort($hasil_aras, function ($a, $b) {
            return $b['Ki'] <=> $a['Ki'];
        });
        $data['hasil_aras'] = $hasil_aras;
        $data['S0'] = $S0;


        // --- RENDER PDF ---
        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // Wajib Landscape karena tabelnya lebar
        $dompdf->render();
        $dompdf->stream("Laporan_Detail_SPK_" . date('Y-m-d') . ".pdf", ["Attachment" => false]);
    }

    // --- RUMUS MOORA ---
    private function hitungMoora($alternatif, $kriteria, $matriks)
    {
        // 1. Normalisasi (Akar Kuadrat)
        $pembagi = [];
        foreach ($kriteria as $k) {
            $sum_kuadrat = 0;
            foreach ($alternatif as $a) {
                $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                $sum_kuadrat += pow($val, 2);
            }
            $pembagi[$k['id_kriteria']] = sqrt($sum_kuadrat);
        }

        // 2. Hitung Nilai Optimasi (Yi)
        $nilai_yi = [];
        foreach ($alternatif as $a) {
            $total_benefit = 0;
            $total_cost = 0;
            foreach ($kriteria as $k) {
                $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                $norm = ($pembagi[$k['id_kriteria']] != 0) ? $val / $pembagi[$k['id_kriteria']] : 0;

                // PENTING: Menggunakan Bobot dari Database (Hasil AHP)
                if ($k['jenis'] == 'benefit') {
                    $total_benefit += ($norm * $k['bobot']);
                } else {
                    $total_cost += ($norm * $k['bobot']);
                }
            }
            $nilai_yi[] = [
                'nis' => $a['nis'],
                'nama' => $a['nama_siswa'],
                'kelas' => $a['kelas'],
                'nilai' => $total_benefit - $total_cost
            ];
        }

        // Sort Ranking (Besar ke Kecil)
        usort($nilai_yi, function ($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });
        return $nilai_yi;
    }

    // --- RUMUS ARAS ---
    private function hitungAras($alternatif, $kriteria, $matriks)
    {
        // 1. Tentukan Nilai A0 (Alternatif Optimum/Ideal)
        $A0 = [];
        foreach ($kriteria as $k) {
            $col_values = [];
            foreach ($alternatif as $a) {
                $col_values[] = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
            }
            // Jika Benefit: Max, Jika Cost: Min
            if ($k['jenis'] == 'benefit') {
                $A0[$k['id_kriteria']] = !empty($col_values) ? max($col_values) : 0;
            } else {
                $A0[$k['id_kriteria']] = !empty($col_values) ? min($col_values) : 0;
            }
        }

        // 2. Gabung A0 ke Matriks Normalisasi
        $matriks_lengkap = [];
        $matriks_lengkap[0] = $A0; // Index 0 untuk A0
        foreach ($alternatif as $a) {
            foreach ($kriteria as $k) {
                $matriks_lengkap[$a['id_alternatif']][$k['id_kriteria']] = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
            }
        }

        // 3. Hitung Total Kolom (Untuk Pembagi)
        $total_per_kriteria = [];
        foreach ($kriteria as $k) {
            $sum = 0;
            foreach ($matriks_lengkap as $id_alt => $vals) {
                $val = $vals[$k['id_kriteria']];
                if ($k['jenis'] == 'cost') {
                    $val = ($val != 0) ? 1 / $val : 0; // Transformasi Cost (1/x)
                }
                $sum += $val;
            }
            $total_per_kriteria[$k['id_kriteria']] = $sum;
        }

        // 4. Normalisasi & Pembobotan (Mencari Si)
        $nilai_Si = [];
        $S0 = 0; // Nilai Si untuk A0

        foreach ($matriks_lengkap as $id_alt => $vals) {
            $Si = 0;
            foreach ($kriteria as $k) {
                $val = $vals[$k['id_kriteria']];
                if ($k['jenis'] == 'cost')
                    $val = ($val != 0) ? 1 / $val : 0;

                $norm = ($total_per_kriteria[$k['id_kriteria']] != 0) ? $val / $total_per_kriteria[$k['id_kriteria']] : 0;
                $weighted = $norm * $k['bobot'];
                $Si += $weighted;
            }

            if ($id_alt === 0) {
                $S0 = $Si;
            } else {
                // Cari data siswa
                $nama = "";
                $nis = "";
                $kelas = "";
                foreach ($alternatif as $a_real) {
                    if ($a_real['id_alternatif'] == $id_alt) {
                        $nama = $a_real['nama_siswa'];
                        $nis = $a_real['nis'];
                        $kelas = $a_real['kelas'];
                    }
                }

                // 5. Hitung Utility Degree (Ki = Si / S0)
                $Ki = ($S0 != 0) ? $Si / $S0 : 0;

                $nilai_Si[] = [
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas' => $kelas,
                    'nilai' => $Ki
                ];
            }
        }

        // Sort Ranking (Besar ke Kecil)
        usort($nilai_Si, function ($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });
        return $nilai_Si;
    }
}