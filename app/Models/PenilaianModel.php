<?php
namespace App\Models;

use CodeIgniter\Model;

class PenilaianModel extends Model
{
    protected $table = 'penilaian';
    protected $primaryKey = 'id_penilaian';
    protected $allowedFields = ['id_alternatif', 'id_kriteria', 'nilai'];

    // Fungsi khusus untuk mengambil data penilaian lengkap dengan nama alternatif dan kriteria
    public function getPenilaianLengkap()
    {
        return $this->db->table('penilaian')
            ->join('alternatif', 'alternatif.id_alternatif = penilaian.id_alternatif')
            ->join('kriteria', 'kriteria.id_kriteria = penilaian.id_kriteria')
            ->get()->getResultArray();
    }
}

?>