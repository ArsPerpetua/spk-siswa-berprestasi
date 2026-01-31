<?php namespace App\Controllers;

use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen User',
            'users' => $this->userModel->findAll()
        ];
        return view('users/index', $data);
    }

    public function create()
    {
        $data = ['title' => 'Tambah User Baru'];
        return view('users/form', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'username' => 'required|is_unique[users.username]',
            'nama_lengkap' => 'required',
            'password' => 'required|min_length[5]',
            'level' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->save([
            'username'     => $this->request->getPost('username'),
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            // Enkripsi password sebelum disimpan
            'password'     => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'level'        => $this->request->getPost('level'),
        ]);

        return redirect()->to('/users')->with('success', 'User berhasil ditambahkan');
    }

    public function edit($id)
    {
        $data = [
            'title' => 'Edit User',
            'user'  => $this->userModel->find($id)
        ];
        return view('users/form', $data);
    }

    public function update($id)
    {
        // Data yang akan diupdate (Default: Nama, Username, Level)
        $updateData = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'username'     => $this->request->getPost('username'),
            'level'        => $this->request->getPost('level'),
        ];

        // Cek apakah kolom password diisi?
        $password_baru = $this->request->getPost('password');
        if (!empty($password_baru)) {
            // Kalau diisi, kita hash dan masukkan ke array update
            $updateData['password'] = password_hash($password_baru, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $updateData);

        return redirect()->to('/users')->with('success', 'Data user berhasil diupdate');
    }

    public function delete($id)
    {
        $this->userModel->delete($id);
        return redirect()->to('/users')->with('success', 'User dihapus');
    }
}