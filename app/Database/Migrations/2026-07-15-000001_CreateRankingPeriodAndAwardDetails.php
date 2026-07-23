<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRankingPeriodAndAwardDetails extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('periode_ranking')) {
            $this->forge->addField([
                'id_periode' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'nama_periode' => ['type' => 'VARCHAR', 'constraint' => 100],
                'tahun_ajaran' => ['type' => 'VARCHAR', 'constraint' => 20],
                'semester' => ['type' => 'VARCHAR', 'constraint' => 10],
                'is_aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => false],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_periode', true);
            $this->forge->addKey(['tahun_ajaran', 'semester'], false, true);
            $this->forge->createTable('periode_ranking');
        }

        if (! $this->db->tableExists('ranking_lama')) {
            $this->forge->addField([
                'id_ranking_lama' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_periode' => ['type' => 'INT', 'unsigned' => true],
                'id_alternatif' => ['type' => 'INT', 'unsigned' => true],
                'kelas' => ['type' => 'VARCHAR', 'constraint' => 50],
                'ranking_lama' => ['type' => 'INT', 'unsigned' => true],
                'nilai_lama' => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
                'sumber' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'generate'],
                'created_at' => ['type' => 'DATETIME', 'null' => false],
            ]);
            $this->forge->addKey('id_ranking_lama', true);
            $this->forge->addUniqueKey(['id_periode', 'id_alternatif']);
            $this->forge->addKey(['id_periode', 'kelas', 'ranking_lama']);
            $this->forge->createTable('ranking_lama');
        }

        if (! $this->db->tableExists('penilaian_penghargaan')) {
            $this->forge->addField([
                'id_penghargaan' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'id_alternatif' => ['type' => 'INT', 'unsigned' => true],
                'kabupaten' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'provinsi' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'nasional' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'internasional' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => false],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_penghargaan', true);
            $this->forge->addUniqueKey('id_alternatif');
            $this->forge->createTable('penilaian_penghargaan');
        }

        $this->seedDefaultPeriod();
        $this->backfillAwardDetails();
    }

    private function seedDefaultPeriod(): void
    {
        if (! $this->db->tableExists('alternatif') || ! $this->db->tableExists('penilaian')) {
            return;
        }

        $period = $this->db->table('periode_ranking')
            ->where('tahun_ajaran', '2024/2025')
            ->where('semester', 'Genap')
            ->get()->getRowArray();

        if (empty($period)) {
            $this->db->table('periode_ranking')->insert([
                'nama_periode' => 'Ranking Awal 2024/2025 Genap',
                'tahun_ajaran' => '2024/2025',
                'semester' => 'Genap',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $idPeriode = (int) $this->db->insertID();
        } else {
            $idPeriode = (int) $period['id_periode'];
        }

        if ($this->db->table('ranking_lama')->where('id_periode', $idPeriode)->countAllResults() > 0) {
            return;
        }

        $this->generateRankingRows($idPeriode);
    }

    private function generateRankingRows(int $idPeriode): void
    {
        $criteria = $this->db->table('kriteria')
            ->whereIn('kode_kriteria', ['C1', 'C6'])->get()->getResultArray();
        $criteriaByCode = [];
        foreach ($criteria as $criterion) {
            $criteriaByCode[$criterion['kode_kriteria']] = (int) $criterion['id_kriteria'];
        }

        $scores = [];
        if (! empty($criteriaByCode)) {
            $rows = $this->db->table('penilaian')
                ->whereIn('id_kriteria', array_values($criteriaByCode))->get()->getResultArray();
            foreach ($rows as $row) {
                $scores[(int) $row['id_alternatif']][(int) $row['id_kriteria']] = (float) $row['nilai'];
            }
        }

        $byClass = [];
        foreach ($this->db->table('alternatif')->get()->getResultArray() as $student) {
            $id = (int) $student['id_alternatif'];
            $values = [];
            foreach ($criteriaByCode as $idCriterion) {
                if (isset($scores[$id][$idCriterion])) {
                    $values[] = $scores[$id][$idCriterion];
                }
            }
            $student['_nilai_lama'] = $values ? array_sum($values) / count($values) : 60 + (($id * 37) % 3000) / 100;
            $byClass[(string) $student['kelas']][] = $student;
        }

        $insert = [];
        $now = date('Y-m-d H:i:s');
        foreach ($byClass as $class => $students) {
            usort($students, static function ($a, $b) {
                $scoreCompare = $b['_nilai_lama'] <=> $a['_nilai_lama'];
                return $scoreCompare !== 0 ? $scoreCompare : strnatcasecmp($a['nama_siswa'], $b['nama_siswa']);
            });
            foreach ($students as $index => $student) {
                $insert[] = [
                    'id_periode' => $idPeriode,
                    'id_alternatif' => (int) $student['id_alternatif'],
                    'kelas' => $class,
                    'ranking_lama' => $index + 1,
                    'nilai_lama' => round((float) $student['_nilai_lama'], 4),
                    'sumber' => 'generate_nilai_akademik',
                    'created_at' => $now,
                ];
            }
        }
        if ($insert) {
            $this->db->table('ranking_lama')->insertBatch($insert, null, 250);
        }
    }

    private function backfillAwardDetails(): void
    {
        if (! $this->db->tableExists('kriteria') || ! $this->db->tableExists('penilaian')) {
            return;
        }
        if ($this->db->table('penilaian_penghargaan')->countAllResults() > 0) {
            return;
        }
        $c5 = $this->db->table('kriteria')->where('kode_kriteria', 'C5')->get()->getRowArray();
        if (empty($c5)) {
            return;
        }

        $insert = [];
        $now = date('Y-m-d H:i:s');
        $rows = $this->db->table('penilaian')->where('id_kriteria', $c5['id_kriteria'])->get()->getResultArray();
        foreach ($rows as $row) {
            $points = max(0, (int) round((float) $row['nilai']));
            $international = intdiv($points, 8); $points %= 8;
            $national = intdiv($points, 4); $points %= 4;
            $province = intdiv($points, 2); $points %= 2;
            $insert[] = [
                'id_alternatif' => (int) $row['id_alternatif'],
                'kabupaten' => $points,
                'provinsi' => $province,
                'nasional' => $national,
                'internasional' => $international,
                'created_at' => $now,
            ];
        }
        if ($insert) {
            $this->db->table('penilaian_penghargaan')->insertBatch($insert, null, 250);
        }
    }

    public function down()
    {
        foreach (['penilaian_penghargaan', 'ranking_lama', 'periode_ranking'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table);
            }
        }
    }
}
