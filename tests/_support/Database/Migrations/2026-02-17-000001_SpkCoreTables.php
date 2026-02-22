<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class SpkCoreTables extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        $this->forge->addField([
            'id_kriteria' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_kriteria' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nama_kriteria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'bobot' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'default'    => 0,
            ],
            'jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
        ]);
        $this->forge->addKey('id_kriteria', true);
        $this->forge->createTable('kriteria');

        $this->forge->addField([
            'id_alternatif' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nis' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'nama_siswa' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'kelas' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_alternatif', true);
        $this->forge->createTable('alternatif');

        $this->forge->addField([
            'id_penilaian' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_alternatif' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kriteria' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nilai' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id_penilaian', true);
        $this->forge->addKey(['id_alternatif', 'id_kriteria']);
        $this->forge->createTable('penilaian');
    }

    public function down(): void
    {
        $this->forge->dropTable('penilaian');
        $this->forge->dropTable('alternatif');
        $this->forge->dropTable('kriteria');
    }
}

