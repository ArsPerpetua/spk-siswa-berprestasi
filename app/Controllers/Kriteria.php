<?php

namespace App\Controllers;

use App\Models\KriteriaModel;

class Kriteria extends BaseController
{
    protected $kriteriaModel;

    public function __construct()
    {
        $this->kriteriaModel = new KriteriaModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Data Kriteria',
            'kriteria' => $this->kriteriaModel->findAll()
        ];
        return view('kriteria/index', $data);
    }

    public function create()
    {
        $data = ['title' => 'Tambah Kriteria'];
        return view('kriteria/form', $data);
    }

    public function store()
    {
        if (
            !$this->validate([
                'kode_kriteria' => 'required|is_unique[kriteria.kode_kriteria]',
                'nama_kriteria' => 'required',
                'jenis' => 'required',
                'bobot' => 'required|decimal'
            ])
        ) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- VALIDASI TOTAL BOBOT ---
        $bobot_baru = (float) $this->request->getPost('bobot');

        // Ambil total bobot saat ini dari database
        $query = $this->kriteriaModel->selectSum('bobot')->first();
        $total_saat_ini = $query['bobot'] ?? 0;

        if (($total_saat_ini + $bobot_baru) > 1) {
            return redirect()->back()->withInput()->with('error', 'Gagal! Total bobot akan melebihi 1. Total saat ini: ' . $total_saat_ini . '. Sisa yang bisa diinput: ' . (1 - $total_saat_ini));
        }
        // ----------------------------

        $this->kriteriaModel->save([
            'kode_kriteria' => $this->request->getPost('kode_kriteria'),
            'nama_kriteria' => $this->request->getPost('nama_kriteria'),
            'bobot' => $bobot_baru,
            'jenis' => $this->request->getPost('jenis'),
        ]);

        return redirect()->to('/kriteria')->with('success', 'Data kriteria berhasil disimpan!');
    }

    public function edit($id)
    {
        $data = [
            'title' => 'Edit Kriteria',
            'kriteria' => $this->kriteriaModel->find($id)
        ];
        return view('kriteria/form', $data);
    }

    public function update($id)
    {
        if (
            !$this->validate([
                'kode_kriteria' => "required|is_unique[kriteria.kode_kriteria,id_kriteria,{$id}]",
                'nama_kriteria' => 'required',
                'jenis' => 'required',
                'bobot' => 'required|decimal'
            ])
        ) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- VALIDASI TOTAL BOBOT (UPDATE) ---
        $bobot_baru = (float) $this->request->getPost('bobot');

        // Ambil total bobot semua kriteria KECUALI yang sedang diedit
        $query = $this->kriteriaModel->selectSum('bobot')->where('id_kriteria !=', $id)->first();
        $total_lain = $query['bobot'] ?? 0;

        if (($total_lain + $bobot_baru) > 1) {
            return redirect()->back()->withInput()->with('error', 'Gagal! Total bobot akan melebihi 1. Sisa bobot tersedia: ' . (1 - $total_lain));
        }
        // -------------------------------------

        $this->kriteriaModel->update($id, [
            'kode_kriteria' => $this->request->getPost('kode_kriteria'),
            'nama_kriteria' => $this->request->getPost('nama_kriteria'),
            'bobot' => $bobot_baru,
            'jenis' => $this->request->getPost('jenis'),
        ]);

        return redirect()->to('/kriteria')->with('success', 'Data kriteria berhasil diupdate!');
    }

    public function delete($id)
    {
        $this->kriteriaModel->delete($id);
        return redirect()->to('/kriteria')->with('success', 'Data kriteria dihapus!');
    }
}