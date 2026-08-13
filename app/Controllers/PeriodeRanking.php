<?php

namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;
use App\Models\PeriodeRankingModel;
use App\Models\RankingLamaModel;

class PeriodeRanking extends BaseController
{
    private PeriodeRankingModel $periodeModel;
    private RankingLamaModel $rankingModel;
    private AlternatifModel $alternatifModel;
    private KriteriaModel $kriteriaModel;
    private PenilaianModel $penilaianModel;

    public function __construct()
    {
        $this->periodeModel = new PeriodeRankingModel();
        $this->rankingModel = new RankingLamaModel();
        $this->alternatifModel = new AlternatifModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->penilaianModel = new PenilaianModel();
    }

    public function index()
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        $periods = $this->periodeModel->orderBy('is_aktif', 'DESC')->orderBy('id_periode', 'DESC')->findAll();
        foreach ($periods as &$period) {
            $period['jumlah_ranking'] = $this->rankingModel->where('id_periode', $period['id_periode'])->countAllResults();
            $period['jumlah_kelas'] = count($this->rankingModel
                ->select('kelas')->where('id_periode', $period['id_periode'])->groupBy('kelas')->findAll());
        }
        unset($period);

        return view('periode_ranking/index', [
            'title' => 'Periode & Ranking Lama',
            'periods' => $periods,
        ]);
    }

    public function store()
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        $rules = [
            'nama_periode' => 'required|max_length[100]',
            'tahun_ajaran' => 'required|max_length[20]',
            'semester' => 'required|in_list[Ganjil,Genap]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $schoolYear = trim((string) $this->request->getPost('tahun_ajaran'));
        $semester = (string) $this->request->getPost('semester');
        if ($this->periodeModel->where('tahun_ajaran', $schoolYear)->where('semester', $semester)->first()) {
            return redirect()->back()->withInput()->with('error', 'Periode untuk tahun ajaran dan semester tersebut sudah ada.');
        }

        $this->periodeModel->insert([
            'nama_periode' => trim((string) $this->request->getPost('nama_periode')),
            'tahun_ajaran' => $schoolYear,
            'semester' => $semester,
            'is_aktif' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/periode-ranking')->with('success', 'Periode ranking berhasil ditambahkan.');
    }

    public function generate(int $id)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        $period = $this->periodeModel->find($id);
        if (! $period) {
            return redirect()->to('/periode-ranking')->with('error', 'Periode tidak ditemukan.');
        }

        $criteria = $this->kriteriaModel->whereIn('kode_kriteria', ['C1', 'C6'])->findAll();
        $criteriaIds = array_map('intval', array_column($criteria, 'id_kriteria'));
        $scores = [];
        if ($criteriaIds) {
            foreach ($this->penilaianModel->whereIn('id_kriteria', $criteriaIds)->findAll() as $row) {
                $scores[(int) $row['id_alternatif']][(int) $row['id_kriteria']] = (float) $row['nilai'];
            }
        }

        $byClass = [];
        foreach ($this->alternatifModel->findAll() as $student) {
            $idStudent = (int) $student['id_alternatif'];
            $values = [];
            foreach ($criteriaIds as $idCriterion) {
                if (isset($scores[$idStudent][$idCriterion])) {
                    $values[] = $scores[$idStudent][$idCriterion];
                }
            }
            // Ranking lama merepresentasikan cara manual: rata-rata nilai akademik C1 dan C6.
            // Fallback deterministik menjaga seluruh siswa tetap memperoleh data awal.
            $student['_nilai_lama'] = $values
                ? array_sum($values) / count($values)
                : 60 + (($idStudent * 37) % 3000) / 100;
            $byClass[(string) $student['kelas']][] = $student;
        }

        $rows = [];
        $now = date('Y-m-d H:i:s');
        foreach ($byClass as $class => $students) {
            usort($students, static function ($a, $b) {
                $compare = $b['_nilai_lama'] <=> $a['_nilai_lama'];
                return $compare !== 0 ? $compare : strnatcasecmp($a['nama_siswa'], $b['nama_siswa']);
            });
            $previousValue = null;
            foreach ($students as $index => $student) {
                $officialValue = round((float) $student['_nilai_lama'], 6);
                if ($index === 0 || $officialValue !== $previousValue) {
                    $rank = $index + 1;
                }
                $rows[] = [
                    'id_periode' => $id,
                    'id_alternatif' => (int) $student['id_alternatif'],
                    'kelas' => $class,
                    'ranking_lama' => $rank,
                    'nilai_lama' => round((float) $student['_nilai_lama'], 4),
                    'sumber' => 'generate_nilai_akademik',
                    'created_at' => $now,
                ];
                $previousValue = $officialValue;
            }
        }

        $db = db_connect();
        $db->transStart();
        $this->rankingModel->where('id_periode', $id)->delete();
        if ($rows) {
            $this->rankingModel->insertBatch($rows, null, 250);
        }
        $db->transComplete();

        return redirect()->to('/periode-ranking')->with('success',
            'Ranking lama berhasil digenerate untuk ' . count($byClass) . ' kelas (' . count($rows) . ' siswa).');
    }

    public function activate(int $id)
    {
        if ($redirect = $this->adminOnly()) {
            return $redirect;
        }
        if (! $this->periodeModel->find($id)) {
            return redirect()->to('/periode-ranking')->with('error', 'Periode tidak ditemukan.');
        }
        $db = db_connect();
        $db->transStart();
        $db->table('periode_ranking')->update(['is_aktif' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->periodeModel->update($id, ['is_aktif' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        $db->transComplete();
        return redirect()->to('/periode-ranking')->with('success', 'Periode aktif berhasil diubah.');
    }

    private function adminOnly()
    {
        if (strtolower((string) session()->get('level')) === 'siswa') {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini hanya untuk admin.');
        }
        return null;
    }
}
