<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HitungMinimalSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('kriteria')->insertBatch([
            [
                'id_kriteria'   => 1,
                'kode_kriteria' => 'C1',
                'nama_kriteria' => 'Nilai Rapor',
                'bobot'         => 0.500000,
                'jenis'         => 'benefit',
            ],
            [
                'id_kriteria'   => 2,
                'kode_kriteria' => 'C2',
                'nama_kriteria' => 'Penghasilan Ortu',
                'bobot'         => 0.300000,
                'jenis'         => 'cost',
            ],
            [
                'id_kriteria'   => 3,
                'kode_kriteria' => 'C5',
                'nama_kriteria' => 'Penghargaan',
                'bobot'         => 0.200000,
                'jenis'         => 'benefit',
            ],
        ]);

        $this->db->table('alternatif')->insertBatch([
            [
                'id_alternatif' => 1,
                'nis'           => '1001',
                'nama_siswa'    => 'Ani',
                'kelas'         => 'XII-A',
            ],
            [
                'id_alternatif' => 2,
                'nis'           => '1002',
                'nama_siswa'    => 'Budi',
                'kelas'         => 'XII-A',
            ],
        ]);

        $this->db->table('penilaian')->insertBatch([
            ['id_penilaian' => 1, 'id_alternatif' => 1, 'id_kriteria' => 1, 'nilai' => 90],
            ['id_penilaian' => 2, 'id_alternatif' => 1, 'id_kriteria' => 2, 'nilai' => 2],
            ['id_penilaian' => 3, 'id_alternatif' => 2, 'id_kriteria' => 1, 'nilai' => 85],
            ['id_penilaian' => 4, 'id_alternatif' => 2, 'id_kriteria' => 2, 'nilai' => 5],
            ['id_penilaian' => 5, 'id_alternatif' => 1, 'id_kriteria' => 3, 'nilai' => 2],
            ['id_penilaian' => 6, 'id_alternatif' => 2, 'id_kriteria' => 3, 'nilai' => 4],
        ]);

        $this->db->table('periode_ranking')->insert([
            'id_periode' => 1,
            'nama_periode' => 'Ranking Awal Test',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'Genap',
            'is_aktif' => 1,
            'created_at' => '2026-01-01 00:00:00',
        ]);
        $this->db->table('ranking_lama')->insertBatch([
            ['id_ranking_lama' => 1, 'id_periode' => 1, 'id_alternatif' => 1, 'kelas' => 'XII-A', 'ranking_lama' => 1, 'nilai_lama' => 90, 'sumber' => 'test', 'created_at' => '2026-01-01 00:00:00'],
            ['id_ranking_lama' => 2, 'id_periode' => 1, 'id_alternatif' => 2, 'kelas' => 'XII-A', 'ranking_lama' => 2, 'nilai_lama' => 85, 'sumber' => 'test', 'created_at' => '2026-01-01 00:00:00'],
        ]);
    }
}
