<?php namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        // Jika sudah login, lempar ke dashboard (agar tidak perlu login lagi)
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/login');
    }

    public function process()
    {
        $userModel = new UserModel();
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // 1. Cari user berdasarkan username
        $user = $userModel->where('username', $username)->first();

        if ($user) {
            // 2. Cek Password (gunakan password_verify untuk keamanan)
            // Jika password di database belum di-hash (masih polos), 
            // ganti baris ini menjadi: if ($user['password'] == $password)
            if (password_verify($password, $user['password'])) {
                
                // 3. Set Session Data
                $sessionData = [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'level' => $user['level'],
                    'logged_in' => TRUE
                ];
                session()->set($sessionData);

                return redirect()->to(base_url('hitung'));
            } else {
                return redirect()->back()->with('error', 'Password salah!');
            }
        } else {
            return redirect()->back()->with('error', 'Username tidak ditemukan!');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }

    // --- FUNGSI RAHASIA UNTUK MEMBUAT AKUN ADMIN PERTAMA KALI ---
    // Jalankan ini sekali saja lewat browser, lalu hapus kodenya atau abaikan.
    public function buat_admin()
    {
        $userModel = new UserModel();
        
        $userModel->insert([
            'username' => 'admin',
            // Kita hash password "12345" agar aman
            'password' => password_hash('12345', PASSWORD_DEFAULT),
            'nama_lengkap' => 'Administrator Utama',
            'level' => 'admin'
        ]);

        echo "User Admin berhasil dibuat! <br> Username: admin <br> Pass: 12345";
    }
}