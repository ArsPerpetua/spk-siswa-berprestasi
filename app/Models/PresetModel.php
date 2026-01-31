<?php

namespace App\Models;

use CodeIgniter\Model;

class PresetModel extends Model
{
    protected $table = 'presets';
    protected $primaryKey = 'id_preset';
    protected $allowedFields = ['nama_preset', 'deskripsi', 'data_json'];
}