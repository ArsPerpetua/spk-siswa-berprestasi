<?php
namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $alternatifModel;
    protected $kriteriaModel;
    protected $penilaianModel;
    protected $userModel;

    public function __construct()
    {
        $this->alternatifModel = new AlternatifModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->penilaianModel = new PenilaianModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil data dasar
        $kriteria = $this->kriteriaModel->findAll();
        $total_siswa = $this->alternatifModel->countAllResults();

        // Hitung Siswa yang SUDAH dinilai (Distinct ID di tabel penilaian)
        $siswa_dinilai = $db->table('penilaian')->groupBy('id_alternatif')->countAllResults();
        $persentase = ($total_siswa > 0) ? round(($siswa_dinilai / $total_siswa) * 100) : 0;

        // Cek Status AHP (Valid jika total bobot mendekati 1)
        $total_bobot = array_sum(array_column($kriteria, 'bobot'));
        $status_ahp = ($total_bobot >= 0.99 && $total_bobot <= 1.01);

        // 1. Data Statistik Dasar (Kotak-kotak atas)
        $data = [
            'title' => 'Dashboard',
            'jumlah_kriteria' => count($kriteria),
            'jumlah_alternatif' => $total_siswa,
            'jumlah_user' => $this->userModel->countAllResults(),
            'siswa_dinilai' => $siswa_dinilai,
            'persentase_penilaian' => $persentase,
            'status_ahp' => $status_ahp
        ];

        // 2. HITUNG SKOR UNTUK GRAFIK (Diambil 5 Terbaik MOORA)
        // Kita hitung cepat di sini (Copy logika dari Hitung.php)

        $alternatif = $this->alternatifModel->findAll();
        $penilaian = $this->penilaianModel->findAll();

        $grafik_nama = [];
        $grafik_ids = [];
        $grafik_moora = [];
        $grafik_aras = [];

        if (!empty($alternatif) && !empty($penilaian)) {
            // Susun Matriks
            $matriks = [];
            foreach ($penilaian as $row) {
                $matriks[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
            }

            // A. HITUNG MOORA
            // 1. Pembagi
            $pembagi = [];
            foreach ($kriteria as $k) {
                $sum = 0;
                foreach ($alternatif as $a) {
                    $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                    $sum += pow($val, 2);
                }
                $pembagi[$k['id_kriteria']] = sqrt($sum);
            }
            // 2. Nilai Yi
            $hasil_moora = [];
            foreach ($alternatif as $a) {
                $total = 0;
                foreach ($kriteria as $k) {
                    $val = $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0;
                    $norm = ($pembagi[$k['id_kriteria']] != 0) ? $val / $pembagi[$k['id_kriteria']] : 0;
                    if ($k['jenis'] == 'benefit')
                        $total += ($norm * $k['bobot']);
                    else
                        $total -= ($norm * $k['bobot']);
                }
                $hasil_moora[] = ['nama' => $a['nama_siswa'], 'nilai' => $total, 'id' => $a['id_alternatif']];
            }
            // Urutkan MOORA (Terbesar)
            usort($hasil_moora, function ($a, $b) {
                return $b['nilai'] <=> $a['nilai'];
            });

            // Ambil Top 5 untuk Grafik
            $top5 = array_slice($hasil_moora, 0, 5);

            // B. HITUNG ARAS (Hanya untuk 5 orang ini agar grafik nyambung)
            // (Kita hitung ARAS full dulu biar adil normalisasinya, baru ambil nilainya)

            // -- Logika ARAS Singkat --
            $nilai_aras_top5 = [];
            if (!empty($top5)) {
                // Ambil nilai Ki untuk Top 5 Moora (Hitung ARAS parsial saja)
                $nilai_aras_top5 = [];
                foreach ($top5 as $t) {
                    // 1. Ambil data siswa yg ada di Top 5
                    $id_alt = $t['id'];
                    $data_alt = $this->alternatifModel->find($id_alt);

                    // 2. Hitung Si untuk ALTERNATIF
                    $Si = 0;
                    foreach ($kriteria as $k) {
                        $nilai_kriteria = $matriks[$id_alt][$k['id_kriteria']] ?? 0; // Nilai Siswa di Kriteria ini
                        $Si += $nilai_kriteria * $k['bobot']; // Si = SUM(Nilai * Bobot)
                    }

                    $nilai_aras_top5[$id_alt] = $Si;
                }
            }
            //dd($nilai_aras_top5);

            // OKE, KITA PAKAI DATA MOORA SAJA UNTUK GRAFIK "TOP 5 SISWA"
            // Karena membandingkan nilai Yi (bisa negatif) dan Ki (0-1) dalam satu chart kadang bikin grafik jadi aneh skalanya.

            foreach ($top5 as $t) {
                $grafik_nama[] = $t['nama'];
                $grafik_ids[] = $t['id'];
                $grafik_moora[] = number_format($t['nilai'], 4);
                $grafik_aras[] = isset($nilai_aras_top5[$t['id']]) ? number_format($nilai_aras_top5[$t['id']], 4) : 0;
            }
        }

        $data['grafik_nama'] = json_encode($grafik_nama);
        $data['grafik_ids'] = json_encode($grafik_ids);
        $data['grafik_moora'] = json_encode($grafik_moora);
        $data['grafik_aras'] = json_encode($grafik_aras);


        return view('dashboard/index', $data);
    }
}