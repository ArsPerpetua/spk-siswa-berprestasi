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

        $this->forge->addField([
            'id_periode' => ['type' => 'INTEGER', 'auto_increment' => true],
            'nama_periode' => ['type' => 'VARCHAR', 'constraint' => 100],
            'tahun_ajaran' => ['type' => 'VARCHAR', 'constraint' => 20],
            'semester' => ['type' => 'VARCHAR', 'constraint' => 10],
            'is_aktif' => ['type' => 'INTEGER', 'default' => 0],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_periode', true);
        $this->forge->createTable('periode_ranking');

        $this->forge->addField([
            'id_ranking_lama' => ['type' => 'INTEGER', 'auto_increment' => true],
            'id_periode' => ['type' => 'INTEGER'],
            'id_alternatif' => ['type' => 'INTEGER'],
            'kelas' => ['type' => 'VARCHAR', 'constraint' => 50],
            'ranking_lama' => ['type' => 'INTEGER'],
            'nilai_lama' => ['type' => 'DECIMAL', 'constraint' => '12,4'],
            'sumber' => ['type' => 'VARCHAR', 'constraint' => 50],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id_ranking_lama', true);
        $this->forge->createTable('ranking_lama');

        $this->forge->addField([
            'id_penghargaan' => ['type' => 'INTEGER', 'auto_increment' => true],
            'id_alternatif' => ['type' => 'INTEGER'],
            'kabupaten' => ['type' => 'INTEGER', 'default' => 0],
            'provinsi' => ['type' => 'INTEGER', 'default' => 0],
            'nasional' => ['type' => 'INTEGER', 'default' => 0],
            'internasional' => ['type' => 'INTEGER', 'default' => 0],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_penghargaan', true);
        $this->forge->createTable('penilaian_penghargaan');
    }

    public function down(): void
    {
        $this->forge->dropTable('penilaian_penghargaan');
        $this->forge->dropTable('ranking_lama');
        $this->forge->dropTable('periode_ranking');
        $this->forge->dropTable('penilaian');
        $this->forge->dropTable('alternatif');
        $this->forge->dropTable('kriteria');
    }
}
