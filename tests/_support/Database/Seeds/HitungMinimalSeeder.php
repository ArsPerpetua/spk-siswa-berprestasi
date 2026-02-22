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
                'bobot'         => 0.600000,
                'jenis'         => 'benefit',
            ],
            [
                'id_kriteria'   => 2,
                'kode_kriteria' => 'C2',
                'nama_kriteria' => 'Penghasilan Ortu',
                'bobot'         => 0.400000,
                'jenis'         => 'cost',
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
        ]);
    }
}

