<?php

namespace App\Models;

use CodeIgniter\Model;

class PenilaianPenghargaanModel extends Model
{
    protected $table = 'penilaian_penghargaan';
    protected $primaryKey = 'id_penghargaan';
    protected $allowedFields = ['id_alternatif', 'kabupaten', 'provinsi', 'nasional', 'internasional', 'created_at', 'updated_at'];
}
