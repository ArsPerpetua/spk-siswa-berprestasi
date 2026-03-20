<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeUserRoleSiswa extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        // Ensure column can store custom role values like "siswa".
        $this->forge->modifyColumn('users', [
            'level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'siswa',
            ],
        ]);

        // Migrate legacy values to current role vocabulary.
        $this->db->query("UPDATE users SET level = 'siswa' WHERE level IS NULL OR TRIM(level) = '' OR LOWER(level) = 'user'");
    }

    public function down()
    {
        if (!$this->db->tableExists('users')) {
            return;
        }

        // Keep VARCHAR schema for safety; only rollback data vocabulary.
        $this->db->query("UPDATE users SET level = 'user' WHERE LOWER(level) = 'siswa'");
    }
}
