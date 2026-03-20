<?php
namespace App\Models;

use CodeIgniter\Model;

class SiswaRankingHistoryModel extends Model
{
    protected $table = 'siswa_ranking_history';
    protected $primaryKey = 'id_history';
    protected $allowedFields = [
        'id_user',
        'id_alternatif',
        'mode_bobot',
        'moora_rank',
        'moora_nilai',
        'aras_rank',
        'aras_nilai',
        'created_at',
    ];
}
