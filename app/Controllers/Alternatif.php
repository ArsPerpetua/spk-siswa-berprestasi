<?php namespace App\Controllers;

use App\Models\AlternatifModel;

class Alternatif extends BaseController
{
    protected $alternatifModel;

    private const KELAS_OPTIONS = [
        'Kelas X-1',
        'Kelas X-2',
        'Kelas X-3',
        'Kelas X-4',
        'Kelas X-5',
        'Kelas X-6',
        'Kelas X-7',
        'Kelas X-8',
        'Kelas XI_IPS_1',
        'Kelas XI_IPS_2',
        'Kelas XI_IPS_3',
        'Kelas XI_MIPA_1',
        'Kelas XI_MIPA_2',
        'Kelas XI_MIPA_3',
        'Kelas XI_MIPA_4',
        'XII_IPS_1',
        'XII_IPS_2',
        'XII_IPS_3',
        'XII_MIPA_1',
        'XII_MIPA_2',
        'XII_MIPA_3',
        'XII_MIPA_4',
    ];

    public function __construct()
    {
        $this->alternatifModel = new AlternatifModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Data Siswa Berprestasi', // Sesuaikan Judul Skripsi
            'alternatif' => $this->alternatifModel->findAll()
        ];
        return view('alternatif/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Siswa',
            'kelas_options' => self::KELAS_OPTIONS,
        ];
        return view('alternatif/form', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'nis' => 'required|is_unique[alternatif.nis]',
            'nama_siswa' => 'required',
            'kelas' => 'required|in_list[' . implode(',', self::KELAS_OPTIONS) . ']'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->alternatifModel->save([
            'nis' => $this->request->getPost('nis'),
            'nama_siswa' => $this->uppercaseName((string) $this->request->getPost('nama_siswa')),
            'kelas' => trim((string) $this->request->getPost('kelas')),
        ]);

        return redirect()->to('/alternatif')->with('success', 'Data siswa berhasil disimpan!');
    }

    public function edit($id)
    {
        $data = [
            'title' => 'Edit Siswa',
            'alternatif' => $this->alternatifModel->find($id),
            'kelas_options' => self::KELAS_OPTIONS,
        ];
        return view('alternatif/form', $data);
    }

    public function update($id)
    {
        if (!$this->validate([
            'nis' => "required|is_unique[alternatif.nis,id_alternatif,{$id}]",
            'nama_siswa' => 'required',
            'kelas' => 'required|in_list[' . implode(',', self::KELAS_OPTIONS) . ']',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->alternatifModel->update($id, [
            'nis' => $this->request->getPost('nis'),
            'nama_siswa' => $this->uppercaseName((string) $this->request->getPost('nama_siswa')),
            'kelas' => trim((string) $this->request->getPost('kelas')),
        ]);

        return redirect()->to('/alternatif')->with('success', 'Data siswa berhasil diupdate!');
    }

    public function delete($id)
    {
        $this->alternatifModel->delete($id);
        return redirect()->to('/alternatif')->with('success', 'Data siswa dihapus!');
    }

    public function import()
    {
        $file = $this->request->getFile('file_csv');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getClientExtension();
            if ($ext !== 'csv') {
                return redirect()->back()->with('error', 'Format file harus CSV!');
            }

            $filepath = $file->getTempName();
            
            // Deteksi delimiter (koma atau titik koma) dengan membaca baris pertama
            $handle = fopen($filepath, "r");
            $firstLine = fgets($handle);
            fclose($handle);
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

            $handle = fopen($filepath, "r");
            $count = 0;
            $rowIdx = 0;

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $rowIdx++;
                
                // Skip Header jika baris pertama mengandung kata 'NIS' atau 'Nama'
                if ($rowIdx == 1) {
                    if (strpos(strtolower($row[0]), 'nis') !== false || strpos(strtolower($row[1]), 'nama') !== false) {
                        continue;
                    }
                }

                // Validasi jumlah kolom minimal 3 (NIS, Nama, Kelas)
                if (count($row) < 3) continue;

                $nis = trim($row[0]);
                $nama = $this->uppercaseName((string) $row[1]);
                $kelas = trim($row[2]);

                if (!in_array($kelas, self::KELAS_OPTIONS, true)) {
                    continue;
                }

                // Cek duplikasi NIS (Skip jika sudah ada)
                if ($this->alternatifModel->where('nis', $nis)->countAllResults() > 0) {
                    continue; 
                }

                $this->alternatifModel->insert([
                    'nis' => $nis,
                    'nama_siswa' => $nama,
                    'kelas' => $kelas
                ]);
                $count++;
            }
            fclose($handle);

            if ($count > 0) {
                return redirect()->to('/alternatif')->with('success', "$count data siswa berhasil diimport!");
            } else {
                return redirect()->to('/alternatif')->with('warning', "Tidak ada data baru yang diimport (Mungkin duplikat atau format salah).");
            }
        }

        return redirect()->back()->with('error', 'Gagal mengupload file.');
    }

    public function downloadTemplate()
    {
        $filename = 'template_siswa.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv;");

        $file = fopen('php://output', 'w');
        $header = ['NIS', 'Nama Siswa', 'Kelas'];
        fputcsv($file, $header);
        
        fclose($file);
        exit;
    }

    private function uppercaseName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($name, 'UTF-8')
            : strtoupper($name);
    }
}
