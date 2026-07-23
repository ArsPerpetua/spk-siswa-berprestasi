<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodeRankingModel extends Model
{
    protected $table = 'periode_ranking';
    protected $primaryKey = 'id_periode';
    protected $allowedFields = ['nama_periode', 'tahun_ajaran', 'semester', 'is_aktif', 'created_at', 'updated_at'];
}
