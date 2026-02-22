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
            </div>

            <?php foreach ($kriteria as $k): ?>
                <div class="mb-3 row">
                    <label class="col-sm-4 col-form-label fw-bold">
                        <?= $k['nama_kriteria'] ?> (<?= $k['kode_kriteria'] ?>)
                    </label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" name="nilai[<?= $k['id_kriteria'] ?>]" class="form-control"
                            placeholder="Masukkan nilai..." value="<?= $nilai_lama[$k['id_kriteria']] ?? '' ?>" required>
                        <small class="text-muted">Jenis: <?= ucfirst($k['jenis']) ?></small>
                    </div>
                </div>
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

<?= $this->endSection() ?>