<?php
namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;

class Penilaian extends BaseController
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

        $data = [
            'title' => 'Isi Penilaian',
            'alternatif' => $alt,
            'kriteria' => $kriteria,
            'nilai_lama' => $nilai_lama
        ];

        return view('penilaian/form', $data);
    }

    // 3. Simpan Penilaian
    public function save()
    {
        $id_alternatif = $this->request->getPost('id_alternatif');
        $input_nilai = $this->request->getPost('nilai'); // Ini berbentuk Array [id_kriteria => nilai]

        // Hapus dulu nilai lama biar tidak duplikat (Cara paling aman & mudah)
        $this->penilaianModel->where('id_alternatif', $id_alternatif)->delete();

        // Loop setiap kriteria yang diinput
        foreach ($input_nilai as $id_kriteria => $nilai) {
            $this->penilaianModel->insert([
                'id_alternatif' => $id_alternatif,
                'id_kriteria' => $id_kriteria,
                'nilai' => $nilai
            ]);
        }

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
        fputcsv($file, $header);

        // 2. Isi Baris dengan Data Siswa (Agar user tinggal isi nilai)
        $alternatif = $this->alternatifModel->findAll();
        foreach ($alternatif as $a) {
            $row = [$a['nis'], $a['nama_siswa']];
            // Kosongkan kolom nilai
            foreach ($kriteria as $k)
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
            foreach ($header as $idx => $colName) {
                $colName = strtoupper(trim($colName));
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

                // Loop setiap kolom nilai (C1, C2...)
                foreach ($colIndexToId as $idx => $id_kriteria) {
                    $nilai = isset($row[$idx]) ? floatval(str_replace(',', '.', $row[$idx])) : 0;

                    // Hapus nilai lama & Insert baru (Upsert manual)
                    $this->penilaianModel->where('id_alternatif', $siswa['id_alternatif'])->where('id_kriteria', $id_kriteria)->delete();
                    $this->penilaianModel->insert(['id_alternatif' => $siswa['id_alternatif'], 'id_kriteria' => $id_kriteria, 'nilai' => $nilai]);
                }
                $count++;
            }
            fclose($handle);
            return redirect()->to('/penilaian')->with('success', "Penilaian untuk $count siswa berhasil diimport!");
        }
        return redirect()->back()->with('error', 'Gagal upload file.');
    }
}