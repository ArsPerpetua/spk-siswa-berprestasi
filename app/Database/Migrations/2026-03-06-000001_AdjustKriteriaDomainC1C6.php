<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdjustKriteriaDomainC1C6 extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('kriteria')) {
            return;
        }

        $kriteriaTable = $this->db->table('kriteria');

        $c1 = $this->db->table('kriteria')
            ->where('kode_kriteria', 'C1')
            ->get()
            ->getRowArray();

        if (!empty($c1)) {
            $kriteriaTable
                ->where('id_kriteria', (int) $c1['id_kriteria'])
                ->update([
                    'nama_kriteria' => 'Rata-rata Mapel Umum/Non-Inti',
                ]);
        }

        $c6 = $this->db->table('kriteria')
            ->where('kode_kriteria', 'C6')
            ->get()
            ->getRowArray();

        if (empty($c6)) {
            $kriteriaTable->insert([
                'kode_kriteria' => 'C6',
                'nama_kriteria' => 'Rata-rata Mapel Inti',
                'bobot' => 0,
                'jenis' => 'benefit',
            ]);
            return;
        }

        $kriteriaTable
            ->where('id_kriteria', (int) $c6['id_kriteria'])
            ->update([
                'nama_kriteria' => 'Rata-rata Mapel Inti',
                'jenis' => 'benefit',
            ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('kriteria')) {
            return;
        }

        $c1 = $this->db->table('kriteria')
            ->where('kode_kriteria', 'C1')
            ->get()
            ->getRowArray();

        if (!empty($c1)) {
            $this->db->table('kriteria')
                ->where('id_kriteria', (int) $c1['id_kriteria'])
                ->update([
                    'nama_kriteria' => 'Nilai Rata-rata',
                ]);
        }

        $c6 = $this->db->table('kriteria')
            ->where('kode_kriteria', 'C6')
            ->get()
            ->getRowArray();

        if (empty($c6)) {
            return;
        }

        if ($this->db->tableExists('penilaian')) {
            $this->db->table('penilaian')
                ->where('id_kriteria', (int) $c6['id_kriteria'])
                ->delete();
        }

        $this->db->table('kriteria')
            ->where('id_kriteria', (int) $c6['id_kriteria'])
            ->delete();
    }
}
