<?php
namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;
use App\Models\SiswaRankingHistoryModel;

class Siswa extends BaseController
{
    protected $alternatifModel;
    protected $kriteriaModel;
    protected $penilaianModel;
    protected $historyModel;

    public function __construct()
    {
        $this->alternatifModel = new AlternatifModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->penilaianModel = new PenilaianModel();
        $this->historyModel = new SiswaRankingHistoryModel();
    }

    public function rankingSaya()
    {
        if (strtolower((string) session()->get('level')) !== 'siswa') {
            return redirect()->to('/dashboard')->with('error', 'Halaman ini khusus role siswa.');
        }

        $allKriteria = $this->kriteriaModel->findAll();
        if (empty($allKriteria)) {
            return view('siswa/ranking_saya', [
                'title' => 'Ranking & Nilai Saya',
                'error_msg' => 'Data kriteria belum tersedia.',
            ]);
        }

        $mode = strtolower(trim((string) $this->request->getGet('mode')));
        $mode = in_array($mode, ['ahp', 'equal'], true) ? $mode : 'ahp';

        $kriteria = $allKriteria;
        $isAHPValid = $this->isAHPValid($allKriteria);
        if ($mode === 'ahp' && !$isAHPValid) {
            $mode = 'equal';
        }
        if ($mode === 'equal') {
            $kriteria = $this->applyEqualWeight($allKriteria);
        }

        $alternatif = $this->alternatifModel
            ->orderBy('kelas', 'ASC')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();
        $penilaian = $this->penilaianModel->findAll();

        $matriks = [];
        foreach ($penilaian as $row) {
            $matriks[(int) $row['id_alternatif']][(int) $row['id_kriteria']] = (float) $row['nilai'];
        }
        $jumlahKriteria = count($kriteria);
        [$eligible, $missingByAlt] = $this->buildEligibleAlternatif($alternatif, $kriteria, $matriks);

        if (empty($eligible)) {
            return view('siswa/ranking_saya', [
                'title' => 'Ranking & Nilai Saya',
                'error_msg' => 'Belum ada data penilaian lengkap untuk dihitung.',
            ]);
        }

        $hasilMoora = $this->hitungMooraRingkas($eligible, $kriteria, $matriks);
        $hasilAras = $this->hitungArasRingkas($eligible, $kriteria, $matriks);

        $myAlt = $this->findSiswaAlternatif($alternatif);
        $myMoora = null;
        $myAras = null;
        $myMooraRank = null;
        $myArasRank = null;
        $myFound = $myAlt !== null;
        $myEligible = false;
        $missingKriteria = [];
        $mapWarning = null;

        if ($myAlt !== null) {
            $myId = (int) $myAlt['id_alternatif'];
            $missingKriteria = $missingByAlt[$myId] ?? [];
            if (trim((string) ($myAlt['nis'] ?? '')) !== trim((string) session()->get('username'))) {
                $mapWarning = 'Akun siswa belum memakai username NIS. Disarankan ubah username agar mapping data akurat.';
            }
            foreach ($hasilMoora as $idx => $row) {
                if ((int) $row['id_alternatif'] === $myId) {
                    $myMoora = $row;
                    $myMooraRank = $idx + 1;
                    $myEligible = true;
                    break;
                }
            }
            foreach ($hasilAras as $idx => $row) {
                if ((int) $row['id_alternatif'] === $myId) {
                    $myAras = $row;
                    $myArasRank = $idx + 1;
                    $myEligible = true;
                    break;
                }
            }
        }

        $kelasTopMoora = [];
        $kelasTopAras = [];
        $kelasMyMooraRank = null;
        $kelasMyArasRank = null;
        if ($myEligible) {
            $kelas = (string) ($myAlt['kelas'] ?? '');
            $kelasMoora = array_values(array_filter($hasilMoora, static fn($r) => (string) ($r['kelas'] ?? '') === $kelas));
            $kelasAras = array_values(array_filter($hasilAras, static fn($r) => (string) ($r['kelas'] ?? '') === $kelas));
            $kelasTopMoora = array_slice($kelasMoora, 0, 10);
            $kelasTopAras = array_slice($kelasAras, 0, 10);

            foreach ($kelasMoora as $idx => $r) {
                if ((int) $r['id_alternatif'] === (int) $myAlt['id_alternatif']) {
                    $kelasMyMooraRank = $idx + 1;
                    break;
                }
            }
            foreach ($kelasAras as $idx => $r) {
                if ((int) $r['id_alternatif'] === (int) $myAlt['id_alternatif']) {
                    $kelasMyArasRank = $idx + 1;
                    break;
                }
            }
        }

        $detailKriteria = [];
        if ($myEligible) {
            $detailKriteria = $this->buildMyDetailKriteria((int) $myAlt['id_alternatif'], $kriteria, $matriks, $eligible);
            $this->storeHistorySnapshot(
                (int) session()->get('id_user'),
                (int) $myAlt['id_alternatif'],
                $mode,
                $myMooraRank,
                (float) ($myMoora['nilai'] ?? 0),
                $myArasRank,
                (float) ($myAras['nilai'] ?? 0)
            );
        }
        $history = $this->fetchHistory((int) session()->get('id_user'));

        $simulasi = $this->buildSimulasi($myAlt, $kriteria, $matriks, $eligible, $mode, $myEligible);

        return view('siswa/ranking_saya', [
            'title' => 'Ranking & Nilai Saya',
            'mode' => $mode,
            'is_ahp_valid' => $isAHPValid,
            'my_alt' => $myAlt,
            'my_found' => $myFound,
            'my_eligible' => $myEligible,
            'my_missing_kriteria' => $missingKriteria,
            'map_warning' => $mapWarning,
            'my_moora' => $myMoora,
            'my_aras' => $myAras,
            'my_moora_rank' => $myMooraRank,
            'my_aras_rank' => $myArasRank,
            'top_moora' => array_slice($hasilMoora, 0, 10),
            'top_aras' => array_slice($hasilAras, 0, 10),
            'kelas_top_moora' => $kelasTopMoora,
            'kelas_top_aras' => $kelasTopAras,
            'kelas_my_moora_rank' => $kelasMyMooraRank,
            'kelas_my_aras_rank' => $kelasMyArasRank,
            'detail_kriteria' => $detailKriteria,
            'history' => $history,
            'simulasi' => $simulasi,
        ]);
    }

    private function isAHPValid(array $kriteria): bool
    {
        $total = array_sum(array_column($kriteria, 'bobot'));
        return $total >= 0.99 && $total <= 1.01;
    }

    private function applyEqualWeight(array $kriteria): array
    {
        $n = count($kriteria);
        if ($n <= 0) {
            return $kriteria;
        }
        $w = 1 / $n;
        foreach ($kriteria as &$k) {
            $k['bobot'] = $w;
        }
        unset($k);
        return $kriteria;
    }

    private function findSiswaAlternatif(array $alternatif): ?array
    {
        $username = trim((string) session()->get('username'));
        $nama = trim((string) session()->get('nama_lengkap'));

        foreach ($alternatif as $a) {
            if (trim((string) ($a['nis'] ?? '')) === $username) {
                return $a;
            }
        }

        if ($nama !== '') {
            $target = mb_strtolower($nama, 'UTF-8');
            foreach ($alternatif as $a) {
                $namaSiswa = mb_strtolower(trim((string) ($a['nama_siswa'] ?? '')), 'UTF-8');
                if ($namaSiswa === $target) {
                    return $a;
                }
            }
        }

        return null;
    }

    private function buildEligibleAlternatif(array $alternatif, array $kriteria, array $matriks): array
    {
        $requiredIds = array_map(static fn($k) => (int) $k['id_kriteria'], $kriteria);
        $requiredSet = array_flip($requiredIds);
        $eligible = [];
        $missingByAlt = [];

        foreach ($alternatif as $a) {
            $idAlt = (int) $a['id_alternatif'];
            $missing = [];
            foreach ($requiredSet as $idK => $_) {
                if (!isset($matriks[$idAlt][$idK])) {
                    $missing[] = $idK;
                }
            }
            $missingByAlt[$idAlt] = $missing;
            if (empty($missing)) {
                $eligible[] = $a;
            }
        }
        return [$eligible, $missingByAlt];
    }

    private function buildMyDetailKriteria(int $idAlt, array $kriteria, array $matriks, array $eligible): array
    {
        $pembagiMoora = [];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $sum = 0;
            foreach ($eligible as $a) {
                $idA = (int) $a['id_alternatif'];
                $val = (float) ($matriks[$idA][$idK] ?? 0);
                $sum += $val * $val;
            }
            $pembagiMoora[$idK] = sqrt($sum);
        }

        $arasFull = [0 => []];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $vals = [];
            foreach ($eligible as $a) {
                $vals[] = (float) ($matriks[(int) $a['id_alternatif']][$idK] ?? 0);
            }
            $arasFull[0][$idK] = (($k['jenis'] ?? 'benefit') === 'benefit') ? (max($vals) ?: 0) : (min($vals) ?: 0);
        }
        foreach ($eligible as $a) {
            $idA = (int) $a['id_alternatif'];
            foreach ($kriteria as $k) {
                $idK = (int) $k['id_kriteria'];
                $arasFull[$idA][$idK] = (float) ($matriks[$idA][$idK] ?? 0);
            }
        }

        $arasTotalKolom = [];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $sum = 0;
            foreach ($arasFull as $vals) {
                $v = (float) ($vals[$idK] ?? 0);
                if (($k['jenis'] ?? 'benefit') === 'cost') {
                    $v = $v != 0 ? 1 / $v : 0;
                }
                $sum += $v;
            }
            $arasTotalKolom[$idK] = $sum;
        }

        $rows = [];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $raw = (float) ($matriks[$idAlt][$idK] ?? 0);
            $mooraNorm = ($pembagiMoora[$idK] ?? 0) != 0 ? ($raw / $pembagiMoora[$idK]) : 0;
            $mooraWeighted = $mooraNorm * (float) $k['bobot'];

            $arasTransform = (($k['jenis'] ?? 'benefit') === 'cost') ? (($raw != 0) ? 1 / $raw : 0) : $raw;
            $arasNorm = ($arasTotalKolom[$idK] ?? 0) != 0 ? ($arasTransform / $arasTotalKolom[$idK]) : 0;
            $arasWeighted = $arasNorm * (float) $k['bobot'];

            $rows[] = [
                'kode' => (string) $k['kode_kriteria'],
                'nama' => (string) $k['nama_kriteria'],
                'jenis' => (string) $k['jenis'],
                'bobot' => (float) $k['bobot'],
                'raw' => $raw,
                'moora_weighted' => $mooraWeighted,
                'aras_weighted' => $arasWeighted,
            ];
        }
        return $rows;
    }

    private function buildSimulasi(?array $myAlt, array $kriteria, array $matriks, array $eligible, string $mode, bool $myEligible): array
    {
        $default = [
            'enabled' => false,
            'inputs' => [],
            'result' => null,
            'error' => null,
        ];
        if (!$myEligible || $myAlt === null) {
            return $default;
        }

        $idAlt = (int) $myAlt['id_alternatif'];
        $inputs = [];
        foreach ($kriteria as $k) {
            $kode = strtoupper((string) $k['kode_kriteria']);
            $inputs[$kode] = (float) ($matriks[$idAlt][(int) $k['id_kriteria']] ?? 0);
        }

        $run = $this->request->getGet('simulate') === '1';
        if (!$run) {
            return array_merge($default, ['inputs' => $inputs]);
        }

        foreach ($kriteria as $k) {
            $kode = strtoupper((string) $k['kode_kriteria']);
            $val = trim((string) $this->request->getGet('sim_' . strtolower($kode)));
            if ($val === '') {
                continue;
            }
            if (!is_numeric(str_replace(',', '.', $val))) {
                return [
                    'enabled' => true,
                    'inputs' => $inputs,
                    'result' => null,
                    'error' => 'Nilai simulasi untuk ' . $kode . ' tidak valid.',
                ];
            }
            $inputs[$kode] = (float) str_replace(',', '.', $val);
        }

        $matriksSim = $matriks;
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $kode = strtoupper((string) $k['kode_kriteria']);
            $matriksSim[$idAlt][$idK] = (float) ($inputs[$kode] ?? ($matriks[$idAlt][$idK] ?? 0));
        }

        $hasilMooraSim = $this->hitungMooraRingkas($eligible, $kriteria, $matriksSim);
        $hasilArasSim = $this->hitungArasRingkas($eligible, $kriteria, $matriksSim);
        $mooraRank = null;
        $arasRank = null;
        $mooraVal = 0.0;
        $arasVal = 0.0;
        foreach ($hasilMooraSim as $i => $r) {
            if ((int) $r['id_alternatif'] === $idAlt) {
                $mooraRank = $i + 1;
                $mooraVal = (float) $r['nilai'];
                break;
            }
        }
        foreach ($hasilArasSim as $i => $r) {
            if ((int) $r['id_alternatif'] === $idAlt) {
                $arasRank = $i + 1;
                $arasVal = (float) $r['nilai'];
                break;
            }
        }

        return [
            'enabled' => true,
            'inputs' => $inputs,
            'result' => [
                'mode' => $mode,
                'moora_rank' => $mooraRank,
                'moora_nilai' => $mooraVal,
                'aras_rank' => $arasRank,
                'aras_nilai' => $arasVal,
            ],
            'error' => null,
        ];
    }

    private function storeHistorySnapshot(
        int $idUser,
        int $idAlternatif,
        string $mode,
        ?int $mooraRank,
        float $mooraNilai,
        ?int $arasRank,
        float $arasNilai
    ): void {
        if ($idUser <= 0 || $idAlternatif <= 0) {
            return;
        }

        $last = $this->historyModel
            ->where('id_user', $idUser)
            ->orderBy('id_history', 'DESC')
            ->first();
        $now = date('Y-m-d H:i:s');
        if (!empty($last)) {
            $sameMode = (string) ($last['mode_bobot'] ?? '') === $mode;
            $sameMoora = (int) ($last['moora_rank'] ?? 0) === (int) ($mooraRank ?? 0);
            $sameAras = (int) ($last['aras_rank'] ?? 0) === (int) ($arasRank ?? 0);
            $sameDay = substr((string) ($last['created_at'] ?? ''), 0, 10) === substr($now, 0, 10);
            if ($sameMode && $sameMoora && $sameAras && $sameDay) {
                return;
            }
        }

        $this->historyModel->insert([
            'id_user' => $idUser,
            'id_alternatif' => $idAlternatif,
            'mode_bobot' => $mode,
            'moora_rank' => $mooraRank,
            'moora_nilai' => $mooraNilai,
            'aras_rank' => $arasRank,
            'aras_nilai' => $arasNilai,
            'created_at' => $now,
        ]);
    }

    private function fetchHistory(int $idUser): array
    {
        $db = db_connect();
        if ($idUser <= 0 || !$db->tableExists('siswa_ranking_history')) {
            return [];
        }
        return $this->historyModel
            ->where('id_user', $idUser)
            ->orderBy('id_history', 'DESC')
            ->findAll(15);
    }

    private function hitungMooraRingkas(array $alternatif, array $kriteria, array $matriks): array
    {
        $pembagi = [];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $sum = 0;
            foreach ($alternatif as $a) {
                $idA = (int) $a['id_alternatif'];
                $val = $matriks[$idA][$idK] ?? 0;
                $sum += ($val * $val);
            }
            $pembagi[$idK] = sqrt($sum);
        }

        $hasil = [];
        foreach ($alternatif as $a) {
            $idA = (int) $a['id_alternatif'];
            $benefit = 0;
            $cost = 0;
            foreach ($kriteria as $k) {
                $idK = (int) $k['id_kriteria'];
                $val = $matriks[$idA][$idK] ?? 0;
                $norm = ($pembagi[$idK] ?? 0) != 0 ? ($val / $pembagi[$idK]) : 0;
                $weighted = $norm * (float) $k['bobot'];
                if (($k['jenis'] ?? 'benefit') === 'benefit') {
                    $benefit += $weighted;
                } else {
                    $cost += $weighted;
                }
            }
            $hasil[] = [
                'id_alternatif' => $idA,
                'nama' => (string) $a['nama_siswa'],
                'nis' => (string) $a['nis'],
                'kelas' => (string) ($a['kelas'] ?? ''),
                'nilai' => $benefit - $cost,
            ];
        }

        usort($hasil, static fn($a, $b) => $b['nilai'] <=> $a['nilai']);
        return $hasil;
    }

    private function hitungArasRingkas(array $alternatif, array $kriteria, array $matriks): array
    {
        $A0 = [];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $col = [];
            foreach ($alternatif as $a) {
                $idA = (int) $a['id_alternatif'];
                $col[] = $matriks[$idA][$idK] ?? 0;
            }
            if (($k['jenis'] ?? 'benefit') === 'benefit') {
                $A0[$idK] = !empty($col) ? max($col) : 0;
            } else {
                $A0[$idK] = !empty($col) ? min($col) : 0;
            }
        }

        $matriksFull = [0 => $A0];
        foreach ($alternatif as $a) {
            $idA = (int) $a['id_alternatif'];
            foreach ($kriteria as $k) {
                $idK = (int) $k['id_kriteria'];
                $matriksFull[$idA][$idK] = $matriks[$idA][$idK] ?? 0;
            }
        }

        $totalKolom = [];
        foreach ($kriteria as $k) {
            $idK = (int) $k['id_kriteria'];
            $sum = 0;
            foreach ($matriksFull as $vals) {
                $val = $vals[$idK] ?? 0;
                if (($k['jenis'] ?? 'benefit') === 'cost') {
                    $val = ($val != 0) ? (1 / $val) : 0;
                }
                $sum += $val;
            }
            $totalKolom[$idK] = $sum;
        }

        $arasTerbobot = [];
        foreach ($matriksFull as $idA => $vals) {
            foreach ($kriteria as $k) {
                $idK = (int) $k['id_kriteria'];
                $val = $vals[$idK] ?? 0;
                if (($k['jenis'] ?? 'benefit') === 'cost') {
                    $val = ($val != 0) ? (1 / $val) : 0;
                }
                $norm = ($totalKolom[$idK] ?? 0) != 0 ? ($val / $totalKolom[$idK]) : 0;
                $arasTerbobot[$idA][$idK] = $norm * (float) $k['bobot'];
            }
        }

        $S0 = isset($arasTerbobot[0]) ? array_sum($arasTerbobot[0]) : 0;
        $hasil = [];
        foreach ($alternatif as $a) {
            $idA = (int) $a['id_alternatif'];
            $Si = isset($arasTerbobot[$idA]) ? array_sum($arasTerbobot[$idA]) : 0;
            $Ki = $S0 != 0 ? ($Si / $S0) : 0;
            $hasil[] = [
                'id_alternatif' => $idA,
                'nama' => (string) $a['nama_siswa'],
                'nis' => (string) $a['nis'],
                'kelas' => (string) ($a['kelas'] ?? ''),
                'nilai' => $Ki,
            ];
        }
        usort($hasil, static fn($a, $b) => $b['nilai'] <=> $a['nilai']);
        return $hasil;
    }
}
