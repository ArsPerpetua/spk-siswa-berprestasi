<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Periode & Ranking Lama</h4>
        <div class="text-muted">Ranking lama digenerate per kelas dari rata-rata nilai akademik C1 dan C6, tanpa MOORA/ARAS.</div>
    </div>
    <a href="<?= base_url('hitung') ?>" class="btn btn-outline-primary"><i class="bi bi-bar-chart"></i> Lihat Perbandingan</a>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div>
<?php endif ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">Tambah Periode</div>
            <div class="card-body">
                <form method="post" action="<?= base_url('periode-ranking/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Periode</label>
                        <input name="nama_periode" class="form-control" value="<?= esc(old('nama_periode')) ?>" placeholder="Ranking Awal 2025/2026 Ganjil" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran</label>
                        <input name="tahun_ajaran" class="form-control" value="<?= esc(old('tahun_ajaran')) ?>" placeholder="2025/2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap" <?= old('semester') === 'Genap' ? 'selected' : '' ?>>Genap</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Tambah Periode</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold">Daftar Periode</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Periode</th><th>Semester</th><th class="text-center">Kelas</th><th class="text-center">Siswa</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php if (empty($periods)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada periode.</td></tr>
                    <?php else: foreach ($periods as $period): ?>
                        <tr>
                            <td><strong><?= esc($period['nama_periode']) ?></strong><br><small class="text-muted"><?= esc($period['tahun_ajaran']) ?></small></td>
                            <td><?= esc($period['semester']) ?></td>
                            <td class="text-center"><?= (int) $period['jumlah_kelas'] ?></td>
                            <td class="text-center"><?= (int) $period['jumlah_ranking'] ?></td>
                            <td><?= $period['is_aktif'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Arsip</span>' ?></td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <form method="post" action="<?= base_url('periode-ranking/generate/' . $period['id_periode']) ?>" onsubmit="return confirm('Generate ulang ranking lama periode ini?')">
                                        <?= csrf_field() ?><button class="btn btn-sm btn-warning"><i class="bi bi-shuffle"></i> Generate</button>
                                    </form>
                                    <?php if (! $period['is_aktif']): ?>
                                    <form method="post" action="<?= base_url('periode-ranking/activate/' . $period['id_periode']) ?>">
                                        <?= csrf_field() ?><button class="btn btn-sm btn-outline-success">Aktifkan</button>
                                    </form>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
