<?php

namespace App\Models;

use CodeIgniter\Model;

class RankingLamaModel extends Model
{
    protected $table = 'ranking_lama';
    protected $primaryKey = 'id_ranking_lama';
    protected $allowedFields = ['id_periode', 'id_alternatif', 'kelas', 'ranking_lama', 'nilai_lama', 'sumber', 'created_at'];
}
