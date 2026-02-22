<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="mb-3 d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <h3 class="fw-bold text-dark mb-1">Dashboard Analitik</h3>
        <p class="text-muted mb-0">Pantau kesiapan data, progres penilaian, dan performa ranking dalam satu layar.</p>
    </div>
    <div class="text-end">
        <small class="text-muted d-block">Filter Aktif</small>
        <div class="d-flex flex-wrap gap-1 justify-content-end">
            <?php foreach (($active_filters ?? []) as $f): ?>
                <span class="badge text-bg-primary"><?= esc($f) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!$status_ahp): ?>
    <div class="alert alert-danger d-flex align-items-start shadow-sm mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
        <div>
            <strong>Bobot AHP belum valid.</strong> Total bobot saat ini
            <strong><?= number_format($total_bobot ?? 0, 4) ?></strong> (harus mendekati 1.0000).
            <div class="mt-2">
                <a href="<?= base_url('ahp') ?>" class="btn btn-sm btn-danger">Perbaiki Pembobotan</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" action="<?= base_url('dashboard') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Fokus Kelas</label>
                <select name="kelas" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    <?php foreach (($kelas_options ?? []) as $kelas): ?>
                        <option value="<?= esc($kelas) ?>" <?= ($filter_kelas ?? '') === $kelas ? 'selected' : '' ?>>
                            <?= esc($kelas) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Top Data</label>
                <select name="top" class="form-select form-select-sm">
                    <?php foreach ([5, 10, 15, 20] as $n): ?>
                        <option value="<?= $n ?>" <?= ((int) ($filter_top ?? 5) === $n) ? 'selected' : '' ?>>
                            Top <?= $n ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Basis Ranking Grafik</label>
                <select name="basis" class="form-select form-select-sm">
                    <option value="moora" <?= (($filter_basis ?? 'moora') === 'moora') ? 'selected' : '' ?>>MOORA</option>
                    <option value="aras" <?= (($filter_basis ?? '') === 'aras') ? 'selected' : '' ?>>ARAS</option>
                </select>
            </div>
            <div class="col-md-3 d-grid gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan Analisis
                </button>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Total Siswa</div>
                <div class="h2 fw-bold mb-0"><?= $jumlah_alternatif ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Kriteria</div>
                <div class="h2 fw-bold mb-0"><?= $jumlah_kriteria ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-info h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Dinilai Lengkap</div>
                <div class="d-flex justify-content-between align-items-end">
                    <div class="h2 fw-bold mb-0"><?= $siswa_dinilai ?></div>
                    <span class="badge text-bg-info"><?= $persentase_penilaian ?>%</span>
                </div>
                <small class="text-muted">Sebagian: <?= $siswa_dinilai_sebagian ?></small>
                <div class="progress mt-2" style="height:6px;">
                    <div class="progress-bar bg-info" style="width: <?= $persentase_penilaian ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Pengguna</div>
                <div class="h2 fw-bold mb-0"><?= $jumlah_user ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <div class="fw-bold text-primary">
                    <i class="bi bi-bar-chart-fill me-1"></i>
                    Performa Top Siswa (MOORA vs ARAS)
                </div>
                <small class="text-muted">Data yang dihitung: <?= (int) ($eligible_count ?? 0) ?> siswa dengan penilaian lengkap.</small>
            </div>
            <div class="card-body">
                <?php if (!empty(json_decode($grafik_nama ?? '[]', true))): ?>
                    <canvas id="chartTopSiswa" height="140"></canvas>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mb-0">Belum ada data yang cukup untuk divisualisasikan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">
                <div class="fw-bold text-secondary"><i class="bi bi-clipboard2-check-fill me-1"></i> Kesiapan Sistem</div>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span>Status AHP</span>
                    <span class="badge <?= $status_ahp ? 'bg-success' : 'bg-danger' ?>">
                        <?= $status_ahp ? 'Valid' : 'Belum Valid' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Data siswa</span>
                    <span class="badge <?= $jumlah_alternatif > 0 ? 'bg-success' : 'bg-danger' ?>">
                        <?= $jumlah_alternatif > 0 ? 'Siap' : 'Kosong' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Data penilaian lengkap</span>
                    <span class="badge <?= $siswa_dinilai > 0 ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $siswa_dinilai > 0 ? 'Tersedia' : 'Belum Ada' ?>
                    </span>
                </div>
                <div class="alert alert-light border mt-2 mb-0 py-2">
                    Proses ranking paling stabil jika semua siswa sudah dinilai lengkap sesuai jumlah kriteria.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="fw-bold text-dark"><i class="bi bi-table me-1"></i> Progres Penilaian per Kelas</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kelas</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Lengkap</th>
                            <th class="text-center">Sebagian</th>
                            <th class="text-center">Belum</th>
                            <th class="text-center">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kelas_stats)): ?>
                            <?php foreach ($kelas_stats as $k): ?>
                                <tr>
                                    <td><?= esc($k['kelas']) ?></td>
                                    <td class="text-center"><?= (int) $k['total'] ?></td>
                                    <td class="text-center text-success fw-bold"><?= (int) $k['dinilai_lengkap'] ?></td>
                                    <td class="text-center text-warning fw-bold"><?= (int) $k['dinilai_sebagian'] ?></td>
                                    <td class="text-center text-danger fw-bold"><?= (int) $k['belum_dinilai'] ?></td>
                                    <td class="text-center"><?= (int) $k['persen'] ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Belum ada data kelas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="fw-bold text-dark"><i class="bi bi-trophy-fill me-1"></i> Top 10 Ranking (MOORA)</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="8%">#</th>
                            <th>NIS / Nama</th>
                            <th class="text-end">MOORA</th>
                            <th class="text-end">ARAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_moora)): ?>
                            <?php $no = 1;
                            foreach ($top_moora as $row): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $no++ ?></td>
                                    <td>
                                        <div class="small text-muted"><?= esc($row['nis']) ?></div>
                                        <div class="fw-semibold"><?= esc($row['nama']) ?></div>
                                    </td>
                                    <td class="text-end"><?= number_format((float) $row['nilai'], 4) ?></td>
                                    <td class="text-end"><?= number_format((float) ($row['aras'] ?? 0), 4) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Ranking belum tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="fw-bold text-secondary"><i class="bi bi-lightning-charge-fill me-1"></i> Akses Cepat</div>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="<?= base_url('alternatif/create') ?>" class="btn btn-outline-primary text-start">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Siswa
                </a>
                <a href="<?= base_url('penilaian') ?>" class="btn btn-outline-success text-start">
                    <i class="bi bi-pencil-square me-1"></i> Input Penilaian
                </a>
                <a href="<?= base_url('hitung') ?>" class="btn btn-outline-danger text-start">
                    <i class="bi bi-calculator me-1"></i> Hasil & Komparasi
                </a>
                <a href="<?= base_url('ahp') ?>" class="btn btn-outline-warning text-start">
                    <i class="bi bi-diagram-3-fill me-1"></i> Pembobotan AHP
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    const labels = <?= $grafik_nama ?? '[]' ?>;
    const ids = <?= $grafik_ids ?? '[]' ?>;
    const dataMoora = <?= $grafik_moora ?? '[]' ?>;
    const dataAras = <?= $grafik_aras ?? '[]' ?>;

    if (labels.length > 0) {
        const ctx = document.getElementById('chartTopSiswa').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'MOORA (Yi)',
                        data: dataMoora,
                        backgroundColor: 'rgba(37, 99, 235, 0.72)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'ARAS (Ki)',
                        data: dataAras,
                        backgroundColor: 'rgba(22, 163, 74, 0.65)',
                        borderColor: 'rgba(22, 163, 74, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true },
                    x: { ticks: { maxRotation: 0, minRotation: 0 } }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { mode: 'index', intersect: false }
                },
                onHover: (event, chartElement) => {
                    event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                },
                onClick: (event, elements) => {
                    if (!elements.length) return;
                    const idx = elements[0].index;
                    const studentId = ids[idx];
                    if (studentId) {
                        window.location.href = '<?= base_url('penilaian/form') ?>/' + studentId;
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
