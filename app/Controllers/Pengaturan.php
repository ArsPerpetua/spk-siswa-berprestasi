<?php

namespace App\Controllers;

class Pengaturan extends BaseController
{
    public function index()
    {
        // Cek Keamanan: Hanya Admin yang boleh akses
        if (session()->get('level') != 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak! Hanya Admin yang bisa mereset data.');
        }

        $data = ['title' => 'Pengaturan Sistem'];
        return view('pengaturan/index', $data);
    }

    public function resetData()
    {
        // Cek Keamanan Lagi
        if (session()->get('level') != 'admin') {
            return redirect()->to('/dashboard');
        }

        $db = \Config\Database::connect();

        // Matikan pengecekan Foreign Key sementara agar bisa truncate
        $db->disableForeignKeyChecks();

        // Kosongkan tabel (Reset ID kembali ke 1)
        $db->table('penilaian')->truncate();
        $db->table('alternatif')->truncate();

        // Hidupkan kembali pengecekan Foreign Key
        $db->enableForeignKeyChecks();

        return redirect()->to('/pengaturan')->with('success', 'Selesai! Semua data Siswa dan Penilaian telah dihapus bersih.');
    }

    public function backup()
    {
        // Cek Keamanan
        if (session()->get('level') != 'admin') {
            return redirect()->to('/dashboard');
        }

        $db = \Config\Database::connect();
        $tables = ['users', 'kriteria', 'alternatif', 'penilaian', 'presets'];
        $sql = "-- BACKUP DATABASE SPK \n-- Tanggal: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            // Cek tabel ada atau tidak
            if (!$db->tableExists($table))
                continue;

            $sql .= "-- Tabel: $table --\n";
            $sql .= "TRUNCATE TABLE `$table`;\n";
            $query = $db->table($table)->get();
            $rows = $query->getResultArray();

            foreach ($rows as $row) {
                $sql .= "INSERT INTO `$table` VALUES (";
                $values = [];
                foreach ($row as $val) {
                    $values[] = is_null($val) ? "NULL" : $db->escape($val);
                }
                $sql .= implode(", ", $values);
                $sql .= ");\n";
            }
            $sql .= "\n";
        }

        return $this->response->download('backup_spk_' . date('Ymd_His') . '.sql', $sql);
    }
}