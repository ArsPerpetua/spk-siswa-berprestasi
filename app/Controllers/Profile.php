<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $id_user = session()->get('id_user');
        $data = [
            'title' => 'Profil Saya',
            'user' => $this->userModel->find($id_user)
        ];
        return view('profile/index', $data);
    }

    public function update()
    {
        $id_user = session()->get('id_user');
        
        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'username' => $this->request->getPost('username'),
        ];

        // Hanya update password jika diisi
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id_user, $data);

        // Update Session agar nama di header langsung berubah
        session()->set([
            'nama_lengkap' => $data['nama_lengkap'],
            'username' => $data['username']
        ]);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui!');
    }
}