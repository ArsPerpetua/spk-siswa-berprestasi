<?php
namespace App\Controllers;

use App\Models\AlternatifModel;
use App\Models\KriteriaModel;
use App\Models\PenilaianModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $alternatifModel;
    protected $kriteriaModel;
    protected $penilaianModel;
    protected $userModel;

    public function __construct()
    {
        $this->alternatifModel = new AlternatifModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->penilaianModel = new PenilaianModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $kriteria = $this->kriteriaModel->findAll();
        $allAlternatif = $this->alternatifModel
            ->orderBy('kelas', 'ASC')
            ->orderBy('nama_siswa', 'ASC')
            ->findAll();
        $allPenilaian = $this->penilaianModel->findAll();

        $jumlahKriteria = count($kriteria);
        $jumlahAlternatif = count($allAlternatif);
        $jumlahUser = $this->userModel->countAllResults();

        $totalBobot = array_sum(array_column($kriteria, 'bobot'));
        $statusAhp = ($totalBobot >= 0.99 && $totalBobot <= 1.01);

        $kelasOptions = array_values(array_unique(array_filter(array_map(
            static fn($a) => trim((string) ($a['kelas'] ?? '')),
            $allAlternatif
        ))));
        sort($kelasOptions, SORT_NATURAL | SORT_FLAG_CASE);

        $filterKelas = trim((string) $this->request->getGet('kelas'));
        $filterTop = (int) $this->request->getGet('top');
        if ($filterTop <= 0) {
            $filterTop = 5;
        }
        $filterTop = min(max($filterTop, 3), 20);
        $filterBasis = strtolower(trim((string) $this->request->getGet('basis')));
        if (!in_array($filterBasis, ['moora', 'aras'], true)) {
            $filterBasis = 'moora';
        }

        $alternatif = $allAlternatif;
        if ($filterKelas !== '') {
            $alternatif = array_values(array_filter(
                $alternatif,
                static fn($a) => (string) ($a['kelas'] ?? '') === $filterKelas
            ));
        }

        $penilaianByAlt = [];
        $distinctKriteriaByAlt = [];
        foreach ($allPenilaian as $row) {
            $idAlt = (int) $row['id_alternatif'];
            $penilaianByAlt[$idAlt][] = $row;
            $distinctKriteriaByAlt[$idAlt][(int) $row['id_kriteria']] = true;
        }

        $siswaDinilaiSebagian = 0;
        $siswaDinilaiLengkap = 0;
        foreach ($allAlternatif as $a) {
            $idAlt = (int) $a['id_alternatif'];
            $countNilai = isset($distinctKriteriaByAlt[$idAlt]) ? count($distinctKriteriaByAlt[$idAlt]) : 0;
            if ($countNilai > 0) {
                $siswaDinilaiSebagian++;
            }
            if ($jumlahKriteria > 0 && $countNilai >= $jumlahKriteria) {
                $siswaDinilaiLengkap++;
            }
        }

        $persentasePenilaian = $jumlahAlternatif > 0
            ? round(($siswaDinilaiLengkap / $jumlahAlternatif) * 100)
            : 0;

        $kelasStatsMap = [];
        foreach ($allAlternatif as $a) {
            $kelas = (string) ($a['kelas'] ?? '-');
            if (!isset($kelasStatsMap[$kelas])) {
                $kelasStatsMap[$kelas] = [
                    'kelas' => $kelas,
                    'total' => 0,
                    'dinilai_lengkap' => 0,
                    'dinilai_sebagian' => 0,
                    'belum_dinilai' => 0,
                    'persen' => 0,
                ];
            }
            $kelasStatsMap[$kelas]['total']++;
            $idAlt = (int) $a['id_alternatif'];
            $countNilai = isset($distinctKriteriaByAlt[$idAlt]) ? count($distinctKriteriaByAlt[$idAlt]) : 0;
            if ($jumlahKriteria > 0 && $countNilai >= $jumlahKriteria) {
                $kelasStatsMap[$kelas]['dinilai_lengkap']++;
            } elseif ($countNilai > 0) {
                $kelasStatsMap[$kelas]['dinilai_sebagian']++;
            } else {
                $kelasStatsMap[$kelas]['belum_dinilai']++;
            }
        }
        $kelasStats = array_values($kelasStatsMap);
        usort($kelasStats, static fn($a, $b) => strnatcasecmp($a['kelas'], $b['kelas']));
        foreach ($kelasStats as &$k) {
            $k['persen'] = $k['total'] > 0 ? round(($k['dinilai_lengkap'] / $k['total']) * 100) : 0;
        }
        unset($k);

        $eligibleAlternatif = [];
        foreach ($alternatif as $a) {
            $idAlt = (int) $a['id_alternatif'];
            $countNilai = isset($distinctKriteriaByAlt[$idAlt]) ? count($distinctKriteriaByAlt[$idAlt]) : 0;
            if ($jumlahKriteria > 0 && $countNilai >= $jumlahKriteria) {
                $eligibleAlternatif[] = $a;
            }
        }

        $penilaianEligible = [];
        $eligibleIdSet = array_flip(array_map(
            static fn($a) => (int) $a['id_alternatif'],
            $eligibleAlternatif
        ));
        foreach ($allPenilaian as $row) {
            if (isset($eligibleIdSet[(int) $row['id_alternatif']])) {
                $penilaianEligible[] = $row;
            }
        }

        $hasilMoora = [];
        $hasilAras = [];
        if (!empty($eligibleAlternatif) && !empty($penilaianEligible) && !empty($kriteria)) {
            $matriks = [];
            foreach ($penilaianEligible as $row) {
                $matriks[(int) $row['id_alternatif']][(int) $row['id_kriteria']] = (float) $row['nilai'];
            }
            $hasilMoora = $this->hitungMooraRingkas($eligibleAlternatif, $kriteria, $matriks);
            $hasilAras = $this->hitungArasRingkas($eligibleAlternatif, $kriteria, $matriks);
        }

        $arasById = [];
        foreach ($hasilAras as $r) {
            $arasById[(int) $r['id_alternatif']] = (float) $r['nilai'];
        }
        $mooraById = [];
        foreach ($hasilMoora as $r) {
            $mooraById[(int) $r['id_alternatif']] = (float) $r['nilai'];
        }

        $basisData = ($filterBasis === 'aras') ? $hasilAras : $hasilMoora;
        $topData = array_slice($basisData, 0, $filterTop);

        $grafikNama = [];
        $grafikIds = [];
        $grafikMoora = [];
        $grafikAras = [];
        foreach ($topData as $row) {
            $id = (int) $row['id_alternatif'];
            $grafikNama[] = $row['nama'];
            $grafikIds[] = $id;
            $grafikMoora[] = round($mooraById[$id] ?? 0, 4);
            $grafikAras[] = round($arasById[$id] ?? 0, 4);
        }

        $top10Moora = array_slice($hasilMoora, 0, 10);
        foreach ($top10Moora as &$row) {
            $row['aras'] = $arasById[(int) $row['id_alternatif']] ?? 0;
        }
        unset($row);

        $activeFilters = [];
        if ($filterKelas !== '') {
            $activeFilters[] = 'Kelas: ' . $filterKelas;
        }
        $activeFilters[] = 'Top: ' . $filterTop;
        $activeFilters[] = 'Basis ranking: ' . strtoupper($filterBasis);

        $data = [
            'title' => 'Dashboard',
            'jumlah_kriteria' => $jumlahKriteria,
            'jumlah_alternatif' => $jumlahAlternatif,
            'jumlah_user' => $jumlahUser,
            'siswa_dinilai' => $siswaDinilaiLengkap,
            'siswa_dinilai_sebagian' => $siswaDinilaiSebagian,
            'persentase_penilaian' => $persentasePenilaian,
            'status_ahp' => $statusAhp,
            'total_bobot' => $totalBobot,
            'kelas_options' => $kelasOptions,
            'filter_kelas' => $filterKelas,
            'filter_top' => $filterTop,
            'filter_basis' => $filterBasis,
            'active_filters' => $activeFilters,
            'kelas_stats' => $kelasStats,
            'eligible_count' => count($eligibleAlternatif),
            'grafik_nama' => json_encode($grafikNama),
            'grafik_ids' => json_encode($grafikIds),
            'grafik_moora' => json_encode($grafikMoora),
            'grafik_aras' => json_encode($grafikAras),
            'top_moora' => $top10Moora,
        ];

        return view('dashboard/index', $data);
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
