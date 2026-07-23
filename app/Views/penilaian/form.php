<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Penilaian: <?= $alternatif['nama_siswa'] ?></h5>
    </div>
    <div class="card-body">

        <form action="<?= base_url('penilaian/save') ?>" method="post">

            <input type="hidden" name="id_alternatif" value="<?= $alternatif['id_alternatif'] ?>">

            <div class="alert alert-info py-2">
                Silakan masukkan nilai untuk setiap kriteria di bawah ini.
                <br><small class="text-muted">C5 dihitung otomatis: Kabupaten × 1 + Provinsi × 2 + Nasional × 4 + Internasional × 8.</small>
            </div>

            <?php foreach ($kriteria as $k): ?>
                <?php if (strtoupper((string) $k['kode_kriteria']) === 'C5'): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold"><?= kriteria_label($k['nama_kriteria']) ?> (C5)</label>
                    <div class="row g-2">
                        <?php foreach (['kabupaten' => 1, 'provinsi' => 2, 'nasional' => 4, 'internasional' => 8] as $level => $point): ?>
                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label small"><?= ucfirst($level) ?> <span class="badge bg-secondary"><?= $point ?> poin</span></label>
                            <input type="number" min="0" step="1" name="penghargaan[<?= $level ?>]"
                                class="form-control award-count" data-point="<?= $point ?>"
                                value="<?= (int) ($penghargaan[$level] ?? 0) ?>" required>
                        </div>
                        <?php endforeach ?>
                    </div>
                    <div class="alert alert-light border mt-2 py-2 mb-0">Nilai C5 otomatis: <strong id="awardTotal">0</strong> poin</div>
                </div>
                <?php else: ?>
                <div class="mb-3 row">
                    <label class="col-sm-4 col-form-label fw-bold">
                        <?= kriteria_label($k['nama_kriteria']) ?> (<?= $k['kode_kriteria'] ?>)
                    </label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" name="nilai[<?= $k['id_kriteria'] ?>]" class="form-control"
                            placeholder="Masukkan nilai..." value="<?= $nilai_lama[$k['id_kriteria']] ?? '' ?>" required>
                        <small class="text-muted">Jenis: <?= ucfirst($k['jenis']) ?></small>
                    </div>
                </div>
                <?php endif ?>
            <?php endforeach; ?>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?= base_url('penilaian') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan Penilaian
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.award-count');
    const total = document.getElementById('awardTotal');
    const update = () => {
        let points = 0;
        inputs.forEach(input => points += Math.max(0, parseInt(input.value || '0', 10)) * parseInt(input.dataset.point, 10));
        total.textContent = points;
    };
    inputs.forEach(input => input.addEventListener('input', update));
    update();
});
</script>

<?= $this->endSection() ?>
