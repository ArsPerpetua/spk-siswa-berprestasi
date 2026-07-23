<?php
namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;
use App\Models\PenilaianPenghargaanModel;

class Penilaian extends BaseController
{
    protected $alternatifModel;
    protected $kriteriaModel;
    protected $penilaianModel;
    protected $penghargaanModel;

    public function __construct()
    {
        $this->alternatifModel = new AlternatifModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->penilaianModel = new PenilaianModel();
        $this->penghargaanModel = new PenilaianPenghargaanModel();
    }

    // 1. Daftar Alternatif untuk Dinilai
    public function index()
    {
        $alternatif = $this->alternatifModel->findAll();

        // Ambil data penilaian (Sudah dinilai atau belum)
        $data_penilaian = [];
        foreach ($alternatif as $alt) {
            $jumlah_nilai = $this->penilaianModel->where('id_alternatif', $alt['id_alternatif'])->countAllResults();
            $data_penilaian[$alt['id_alternatif']] = ($jumlah_nilai > 0) ? true : false;
        }

        $data = [
            'title' => 'Input Penilaian',
            'alternatif' => $alternatif,
            'data_penilaian' => $data_penilaian
        ];

        return view('penilaian/index', $data);
    }

    // 2. Form Input Nilai (Berdasarkan ID Alternatif)
    public function form($id_alternatif)
    {
        // Ambil data alternatif
        $alt = $this->alternatifModel->find($id_alternatif);

        // Ambil semua kriteria
        $kriteria = $this->kriteriaModel->findAll();

        // Ambil penilaian yang SUDAH ADA (jika mau edit)
        $penilaian_ada = $this->penilaianModel->where('id_alternatif', $id_alternatif)->findAll();

        // Susun penilaian jadi array biar mudah dicek di view
        // Format: [id_kriteria => nilai]
        $nilai_lama = [];
        foreach ($penilaian_ada as $p) {
            $nilai_lama[$p['id_kriteria']] = $p['nilai'];
        }

        $penghargaan = $this->penghargaanModel->where('id_alternatif', $id_alternatif)->first();
        if (! $penghargaan) {
            $c5 = null;
            foreach ($kriteria as $criterion) {
                if (strtoupper((string) $criterion['kode_kriteria']) === 'C5') {
                    $c5 = $nilai_lama[$criterion['id_kriteria']] ?? 0;
                    break;
                }
            }
            $penghargaan = $this->decomposeAwardPoints((int) round((float) $c5));
        }

        $data = [
            'title' => 'Isi Penilaian',
            'alternatif' => $alt,
            'kriteria' => $kriteria,
            'nilai_lama' => $nilai_lama,
            'penghargaan' => $penghargaan,
        ];

        return view('penilaian/form', $data);
    }

    // 3. Simpan Penilaian
    public function save()
    {
        $id_alternatif = $this->request->getPost('id_alternatif');
        $input_nilai = (array) $this->request->getPost('nilai'); // Format: [id_kriteria => nilai]

        $award = $this->sanitizeAwardInput((array) $this->request->getPost('penghargaan'));
        $awardPoints = $this->awardPoints($award);
        $c5 = $this->kriteriaModel->where('kode_kriteria', 'C5')->first();
        if ($c5) {
            $input_nilai[(int) $c5['id_kriteria']] = $awardPoints;
        }

        $db = db_connect();
        $db->transStart();
        $this->penilaianModel->where('id_alternatif', $id_alternatif)->delete();

        // Loop setiap kriteria yang diinput
        foreach ($input_nilai as $id_kriteria => $nilai) {
            $this->penilaianModel->insert([
                'id_alternatif' => $id_alternatif,
                'id_kriteria' => $id_kriteria,
                'nilai' => $nilai
            ]);
        }

        $existingAward = $this->penghargaanModel->where('id_alternatif', $id_alternatif)->first();
        $awardData = array_merge($award, [
            'id_alternatif' => (int) $id_alternatif,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($existingAward) {
            $this->penghargaanModel->update($existingAward['id_penghargaan'], $awardData);
        } else {
            $awardData['created_at'] = date('Y-m-d H:i:s');
            $this->penghargaanModel->insert($awardData);
        }
        $db->transComplete();

        return redirect()->to('/penilaian')->with('success', 'Penilaian berhasil disimpan!');
    }

    public function downloadTemplate()
    {
        $filename = 'template_penilaian.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv;");

        $file = fopen('php://output', 'w');

        // 1. Buat Header (NIS, Nama, C1, C2, ...)
        $header = ['NIS', 'Nama Siswa'];
        $kriteria = $this->kriteriaModel->findAll();
        foreach ($kriteria as $k) {
            $header[] = $k['kode_kriteria']; // C1, C2, dst
        }

        // Kolom subkriteria opsional (untuk preprocessing terstruktur)
        $header[] = 'C1_1_BahasaLiterasi';
        $header[] = 'C1_2_NumerasiDasar';
        $header[] = 'C1_3_KewargaanSejarah';
        $header[] = 'C1_4_PraktikKeterampilan';
        $header[] = 'C3_1_Sakit';
        $header[] = 'C3_2_Izin';
        $header[] = 'C3_3_Alpa';
        $header[] = 'C5_1_Kabupaten';
        $header[] = 'C5_2_Provinsi';
        $header[] = 'C5_3_Nasional';
        $header[] = 'C5_4_Internasional';
        fputcsv($file, $header);

        // 2. Isi Baris dengan Data Siswa (Agar user tinggal isi nilai)
        $alternatif = $this->alternatifModel->findAll();
        foreach ($alternatif as $a) {
            $row = [$a['nis'], $a['nama_siswa']];
            // Kosongkan kolom nilai
            foreach ($kriteria as $k)
                $row[] = '';
            // Kolom subkriteria opsional
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';
            fputcsv($file, $row);
        }

        fclose($file);
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('file_csv');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $filepath = $file->getTempName();

            // Deteksi delimiter (; atau ,)
            $handle = fopen($filepath, "r");
            $firstLine = fgets($handle);
            fclose($handle);
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

            $handle = fopen($filepath, "r");

            // Baca Header untuk mapping kolom C1, C2 ke ID Kriteria
            $header = fgetcsv($handle, 1000, $delimiter);
            $kriteriaMap = []; // Map: 'C1' => id_kriteria
            $dbKriteria = $this->kriteriaModel->findAll();
            foreach ($dbKriteria as $k) {
                $kriteriaMap[strtoupper($k['kode_kriteria'])] = $k['id_kriteria'];
            }

            // Mapping Index CSV ke ID Kriteria
            $colIndexToId = [];
            $headerIndexMap = [];
            foreach ($header as $idx => $colName) {
                $colName = strtoupper(trim($colName));
                $headerIndexMap[$colName] = $idx;
                if (isset($kriteriaMap[$colName])) {
                    $colIndexToId[$idx] = $kriteriaMap[$colName];
                }
            }

            // Baca Data Baris per Baris
            $count = 0;
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $nis = trim($row[0]); // Asumsi Kolom 0 adalah NIS

                // Cari ID Alternatif berdasarkan NIS
                $siswa = $this->alternatifModel->where('nis', $nis)->first();
                if (!$siswa)
                    continue; // Skip jika siswa tidak ditemukan

                $parseNum = static function ($val): ?float {
                    $val = trim((string) $val);
                    if ($val === '') {
                        return null;
                    }
                    return floatval(str_replace(',', '.', $val));
                };

                // Ambil nilai utama per kode dari file
                $nilaiByKode = [];
                foreach ($kriteriaMap as $kode => $idKriteria) {
                    if (!isset($headerIndexMap[$kode])) {
                        continue;
                    }
                    $idx = $headerIndexMap[$kode];
                    $nilaiByKode[$kode] = $parseNum($row[$idx] ?? null);
                }

                // Jika C1 kosong, hitung dari subkriteria C1 (berbobot dan dinormalisasi)
                if (array_key_exists('C1', $nilaiByKode) && $nilaiByKode['C1'] === null) {
                    $c11 = isset($headerIndexMap['C1_1_BAHASALITERASI']) ? $parseNum($row[$headerIndexMap['C1_1_BAHASALITERASI']] ?? null) : null;
                    $c12 = isset($headerIndexMap['C1_2_NUMERASIDASAR']) ? $parseNum($row[$headerIndexMap['C1_2_NUMERASIDASAR']] ?? null) : null;
                    $c13 = isset($headerIndexMap['C1_3_KEWARGAANSEJARAH']) ? $parseNum($row[$headerIndexMap['C1_3_KEWARGAANSEJARAH']] ?? null) : null;
                    $c14 = isset($headerIndexMap['C1_4_PRAKTIKKETERAMPILAN']) ? $parseNum($row[$headerIndexMap['C1_4_PRAKTIKKETERAMPILAN']] ?? null) : null;

                    $parts = [
                        ['v' => $c11, 'w' => 0.35],
                        ['v' => $c12, 'w' => 0.30],
                        ['v' => $c13, 'w' => 0.20],
                        ['v' => $c14, 'w' => 0.15],
                    ];
                    $num = 0.0;
                    $den = 0.0;
                    foreach ($parts as $p) {
                        if ($p['v'] === null) {
                            continue;
                        }
                        $num += $p['v'] * $p['w'];
                        $den += $p['w'];
                    }
                    if ($den > 0) {
                        $nilaiByKode['C1'] = $num / $den;
                    }
                }

                // Jika C3 kosong, hitung dari subkriteria C3 (Sakit+Izin+Alpa)
                if (array_key_exists('C3', $nilaiByKode) && $nilaiByKode['C3'] === null) {
                    $c31 = isset($headerIndexMap['C3_1_SAKIT']) ? $parseNum($row[$headerIndexMap['C3_1_SAKIT']] ?? null) : null;
                    $c32 = isset($headerIndexMap['C3_2_IZIN']) ? $parseNum($row[$headerIndexMap['C3_2_IZIN']] ?? null) : null;
                    $c33 = isset($headerIndexMap['C3_3_ALPA']) ? $parseNum($row[$headerIndexMap['C3_3_ALPA']] ?? null) : null;
                    $sum = 0.0;
                    $hasAny = false;
                    foreach ([$c31, $c32, $c33] as $v) {
                        if ($v === null) {
                            continue;
                        }
                        $sum += $v;
                        $hasAny = true;
                    }
                    if ($hasAny) {
                        $nilaiByKode['C3'] = $sum;
                    }
                }

                $awardColumns = [
                    'kabupaten' => 'C5_1_KABUPATEN',
                    'provinsi' => 'C5_2_PROVINSI',
                    'nasional' => 'C5_3_NASIONAL',
                    'internasional' => 'C5_4_INTERNASIONAL',
                ];
                $award = [];
                $hasAwardDetail = false;
                foreach ($awardColumns as $key => $column) {
                    $value = isset($headerIndexMap[$column]) ? $parseNum($row[$headerIndexMap[$column]] ?? null) : null;
                    if ($value !== null) {
                        $hasAwardDetail = true;
                    }
                    $award[$key] = max(0, (int) round((float) ($value ?? 0)));
                }
                if ($hasAwardDetail && array_key_exists('C5', $nilaiByKode)) {
                    $nilaiByKode['C5'] = $this->awardPoints($award);
                }

                // Upsert nilai utama (C1..C6) ke DB
                foreach ($nilaiByKode as $kode => $nilai) {
                    if ($nilai === null || !isset($kriteriaMap[$kode])) {
                        continue;
                    }
                    $id_kriteria = $kriteriaMap[$kode];

                    // Hapus nilai lama & Insert baru (Upsert manual)
                    $this->penilaianModel->where('id_alternatif', $siswa['id_alternatif'])->where('id_kriteria', $id_kriteria)->delete();
                    $this->penilaianModel->insert(['id_alternatif' => $siswa['id_alternatif'], 'id_kriteria' => $id_kriteria, 'nilai' => $nilai]);
                }
                if ($hasAwardDetail) {
                    $existingAward = $this->penghargaanModel->where('id_alternatif', $siswa['id_alternatif'])->first();
                    $awardData = array_merge($award, [
                        'id_alternatif' => (int) $siswa['id_alternatif'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    if ($existingAward) {
                        $this->penghargaanModel->update($existingAward['id_penghargaan'], $awardData);
                    } else {
                        $awardData['created_at'] = date('Y-m-d H:i:s');
                        $this->penghargaanModel->insert($awardData);
                    }
                }
                $count++;
            }
            fclose($handle);
            return redirect()->to('/penilaian')->with('success', "Penilaian untuk $count siswa berhasil diimport!");
        }
        return redirect()->back()->with('error', 'Gagal upload file.');
    }

    private function sanitizeAwardInput(array $input): array
    {
        $result = [];
        foreach (['kabupaten', 'provinsi', 'nasional', 'internasional'] as $key) {
            $result[$key] = max(0, (int) ($input[$key] ?? 0));
        }
        return $result;
    }

    private function awardPoints(array $award): int
    {
        return ((int) ($award['kabupaten'] ?? 0))
            + (2 * (int) ($award['provinsi'] ?? 0))
            + (4 * (int) ($award['nasional'] ?? 0))
            + (8 * (int) ($award['internasional'] ?? 0));
    }

    private function decomposeAwardPoints(int $points): array
    {
        $points = max(0, $points);
        $international = intdiv($points, 8); $points %= 8;
        $national = intdiv($points, 4); $points %= 4;
        $province = intdiv($points, 2); $points %= 2;
        return [
            'kabupaten' => $points,
            'provinsi' => $province,
            'nasional' => $national,
            'internasional' => $international,
        ];
    }
}
