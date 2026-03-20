<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$mode = $mode ?? 'equal';
$isAHPValid = $is_ahp_valid ?? false;
$myAlt = $my_alt ?? null;
$myFound = $my_found ?? false;
$myEligible = $my_eligible ?? false;
$myMoora = $my_moora ?? null;
$myAras = $my_aras ?? null;
$myMooraRank = $my_moora_rank ?? null;
$myArasRank = $my_aras_rank ?? null;
$kelasTopMoora = $kelas_top_moora ?? [];
$kelasTopAras = $kelas_top_aras ?? [];
$kelasMyMooraRank = $kelas_my_moora_rank ?? null;
$kelasMyArasRank = $kelas_my_aras_rank ?? null;
$detailKriteria = $detail_kriteria ?? [];
$history = $history ?? [];
$simulasi = $simulasi ?? ['enabled' => false, 'inputs' => [], 'result' => null, 'error' => null];
$missingKriteria = $my_missing_kriteria ?? [];
$mapWarning = $map_warning ?? null;
?>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-award me-2"></i> Ranking & Nilai Saya</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?= base_url('siswa/ranking') ?>" class="row g-2 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label class="form-label mb-1">Mode Bobot</label>
                <select name="mode" class="form-select">
                    <option value="ahp" <?= $mode === 'ahp' ? 'selected' : '' ?>>AHP</option>
                    <option value="equal" <?= $mode === 'equal' ? 'selected' : '' ?>>Tanpa AHP (Bobot Sama)</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat me-1"></i> Terapkan
                </button>
            </div>
        </form>
        <small class="text-muted d-block mt-2">
            Mode aktif:
            <span class="badge text-bg-primary"><?= $mode === 'ahp' ? 'AHP' : 'Tanpa AHP' ?></span>
            <?php if (!$isAHPValid): ?>
                <span class="badge text-bg-warning">Bobot AHP belum valid, sistem fallback ke bobot sama</span>
            <?php endif; ?>
        </small>
    </div>
</div>

<?php if (isset($error_msg)): ?>
    <div class="alert alert-warning"><?= esc($error_msg) ?></div>
<?php else: ?>
    <?php if (!$myFound): ?>
        <div class="alert alert-danger">
            Data siswa untuk akun ini tidak ditemukan di data alternatif. Pastikan username akun siswa sama dengan NIS.
        </div>
    <?php endif; ?>

    <?php if ($mapWarning): ?>
        <div class="alert alert-warning"><?= esc($mapWarning) ?></div>
    <?php endif; ?>

    <?php if ($myFound && !$myEligible): ?>
        <div class="alert alert-warning">
            Data penilaian Anda belum lengkap, sehingga belum ikut perhitungan ranking.
            <?php if (!empty($missingKriteria)): ?>
                <div class="small mt-1">Kriteria belum terisi: <?= implode(', ', array_map('intval', $missingKriteria)) ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($myFound && $myEligible): ?>
        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h6 class="text-primary mb-3"><i class="bi bi-trophy me-1"></i> Posisi Saya (Global)</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <div class="small text-muted">MOORA</div>
                                    <div class="h4 mb-0 fw-bold text-primary">#<?= (int) $myMooraRank ?></div>
                                    <div class="small"><?= number_format((float) ($myMoora['nilai'] ?? 0), 6) ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <div class="small text-muted">ARAS</div>
                                    <div class="h4 mb-0 fw-bold text-success">#<?= (int) $myArasRank ?></div>
                                    <div class="small"><?= number_format((float) ($myAras['nilai'] ?? 0), 6) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-3">
                            <strong><?= esc((string) ($myAlt['nama_siswa'] ?? '-')) ?></strong> (<?= esc((string) ($myAlt['nis'] ?? '-')) ?>) - Kelas <?= esc((string) ($myAlt['kelas'] ?? '-')) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 border-info">
                    <div class="card-body">
                        <h6 class="text-info mb-3"><i class="bi bi-people me-1"></i> Posisi Saya di Kelas</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <div class="small text-muted">MOORA (Kelas)</div>
                                    <div class="h4 mb-0 fw-bold text-info">#<?= (int) $kelasMyMooraRank ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border text-center">
                                    <div class="small text-muted">ARAS (Kelas)</div>
                                    <div class="h4 mb-0 fw-bold text-info">#<?= (int) $kelasMyArasRank ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted mt-3">
                            Dibandingkan dengan siswa pada kelas yang sama.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light fw-bold">Top 10 Kelas - MOORA</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 65px;">Rank</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th class="text-end">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kelasTopMoora)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($kelasTopMoora as $i => $row): ?>
                                        <tr class="<?= ((int) $row['id_alternatif'] === (int) ($myAlt['id_alternatif'] ?? 0)) ? 'table-primary' : '' ?>">
                                            <td>#<?= $i + 1 ?></td>
                                            <td><?= esc((string) ($row['nis'] ?? '-')) ?></td>
                                            <td><?= esc((string) ($row['nama'] ?? '-')) ?></td>
                                            <td class="text-end"><?= number_format((float) ($row['nilai'] ?? 0), 6) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light fw-bold">Top 10 Kelas - ARAS</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 65px;">Rank</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th class="text-end">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kelasTopAras)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($kelasTopAras as $i => $row): ?>
                                        <tr class="<?= ((int) $row['id_alternatif'] === (int) ($myAlt['id_alternatif'] ?? 0)) ? 'table-success' : '' ?>">
                                            <td>#<?= $i + 1 ?></td>
                                            <td><?= esc((string) ($row['nis'] ?? '-')) ?></td>
                                            <td><?= esc((string) ($row['nama'] ?? '-')) ?></td>
                                            <td class="text-end"><?= number_format((float) ($row['nilai'] ?? 0), 6) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light fw-bold">Detail Kontribusi Nilai per Kriteria</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Kriteria</th>
                            <th>Jenis</th>
                            <th class="text-end">Bobot</th>
                            <th class="text-end">Nilai Asli</th>
                            <th class="text-end">Kontribusi MOORA</th>
                            <th class="text-end">Kontribusi ARAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailKriteria as $d): ?>
                            <tr>
                                <td class="fw-bold"><?= esc((string) ($d['kode'] ?? '-')) ?></td>
                                <td><?= esc(kriteria_label((string) ($d['nama'] ?? '-'))) ?></td>
                                <td><?= esc(ucfirst((string) ($d['jenis'] ?? '-'))) ?></td>
                                <td class="text-end"><?= number_format((float) ($d['bobot'] ?? 0), 6) ?></td>
                                <td class="text-end"><?= number_format((float) ($d['raw'] ?? 0), 4) ?></td>
                                <td class="text-end"><?= number_format((float) ($d['moora_weighted'] ?? 0), 6) ?></td>
                                <td class="text-end"><?= number_format((float) ($d['aras_weighted'] ?? 0), 6) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light fw-bold">Simulasi Nilai (What-if)</div>
            <div class="card-body">
                <form method="get" action="<?= base_url('siswa/ranking') ?>" class="row g-2">
                    <input type="hidden" name="mode" value="<?= esc($mode) ?>">
                    <input type="hidden" name="simulate" value="1">
                    <?php foreach ($detailKriteria as $d): ?>
                        <?php $kode = strtolower((string) ($d['kode'] ?? '')); ?>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label mb-1"><?= esc((string) ($d['kode'] ?? '-')) ?> - <?= esc(kriteria_label((string) ($d['nama'] ?? '-'))) ?></label>
                            <input type="number" step="0.01" min="0"
                                class="form-control"
                                name="sim_<?= esc($kode) ?>"
                                value="<?= esc((string) ($simulasi['inputs'][strtoupper((string) ($d['kode'] ?? ''))] ?? $d['raw'])) ?>">
                        </div>
                    <?php endforeach; ?>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill me-1"></i> Jalankan Simulasi
                        </button>
                        <a href="<?= base_url('siswa/ranking?mode=' . $mode) ?>" class="btn btn-outline-secondary">
                            Reset Simulasi
                        </a>
                    </div>
                </form>
                <?php if (!empty($simulasi['error'])): ?>
                    <div class="alert alert-danger mt-3 mb-0"><?= esc((string) $simulasi['error']) ?></div>
                <?php elseif (!empty($simulasi['result'])): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        Hasil simulasi:
                        MOORA <strong>#<?= (int) ($simulasi['result']['moora_rank'] ?? 0) ?></strong>
                        (<?= number_format((float) ($simulasi['result']['moora_nilai'] ?? 0), 6) ?>),
                        ARAS <strong>#<?= (int) ($simulasi['result']['aras_rank'] ?? 0) ?></strong>
                        (<?= number_format((float) ($simulasi['result']['aras_nilai'] ?? 0), 6) ?>).
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light fw-bold">Riwayat Posisi Saya (Maks. 15 Snapshot)</div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Mode</th>
                            <th class="text-center">Rank MOORA</th>
                            <th class="text-end">Nilai MOORA</th>
                            <th class="text-center">Rank ARAS</th>
                            <th class="text-end">Nilai ARAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada riwayat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($history as $h): ?>
                                <tr>
                                    <td><?= esc((string) ($h['created_at'] ?? '-')) ?></td>
                                    <td><span class="badge text-bg-secondary"><?= esc(strtoupper((string) ($h['mode_bobot'] ?? '-'))) ?></span></td>
                                    <td class="text-center"><?= (int) ($h['moora_rank'] ?? 0) ?></td>
                                    <td class="text-end"><?= number_format((float) ($h['moora_nilai'] ?? 0), 6) ?></td>
                                    <td class="text-center"><?= (int) ($h['aras_rank'] ?? 0) ?></td>
                                    <td class="text-end"><?= number_format((float) ($h['aras_nilai'] ?? 0), 6) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
