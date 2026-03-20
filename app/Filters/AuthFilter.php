<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah ada session 'logged_in'
        if (!session()->get('logged_in')) {
            // Kalau tidak ada, tendang ke halaman login
            return redirect()->to(base_url('/'))->with('error', 'Silakan login terlebih dahulu!');
        }

        $level = strtolower((string) session()->get('level'));
        if ($level === 'siswa') {
            $path = trim($request->getUri()->getPath(), '/');
            $blockedPrefixes = [
                'kriteria',
                'alternatif',
                'penilaian',
                'ahp',
                'users',
                'pengaturan',
            ];

            foreach ($blockedPrefixes as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                    return redirect()->to(base_url('dashboard'))
                        ->with('error', 'Akses menu ini khusus admin/operator.');
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu dilakukan setelah request
    }
}
