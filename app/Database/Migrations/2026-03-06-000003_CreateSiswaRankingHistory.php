<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSiswaRankingHistory extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('siswa_ranking_history')) {
            return;
        }

        $this->forge->addField([
            'id_history' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'id_alternatif' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'mode_bobot' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'ahp',
            ],
            'moora_rank' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'moora_nilai' => [
                'type' => 'DECIMAL',
                'constraint' => '18,8',
                'null' => true,
            ],
            'aras_rank' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'aras_nilai' => [
                'type' => 'DECIMAL',
                'constraint' => '18,8',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id_history', true);
        $this->forge->addKey(['id_user', 'created_at']);
        $this->forge->addKey(['id_alternatif', 'created_at']);
        $this->forge->createTable('siswa_ranking_history');
    }

    public function down()
    {
        if ($this->db->tableExists('siswa_ranking_history')) {
            $this->forge->dropTable('siswa_ranking_history');
        }
    }
}
