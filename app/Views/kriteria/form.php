<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><?= $title ?></h4>
    <a href="<?= base_url('kriteria') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors'); ?>

        <form action="<?= isset($kriteria) ? base_url('kriteria/update/'.$kriteria['id_kriteria']) : base_url('kriteria/store') ?>" method="post">
            
            <div class="mb-3">
                <label class="form-label">Kode Kriteria</label>
                <input type="text" name="kode_kriteria" class="form-control <?= isset($errors['kode_kriteria']) ? 'is-invalid' : '' ?>" 
                    value="<?= old('kode_kriteria', $kriteria['kode_kriteria'] ?? '') ?>" placeholder="Contoh: C1">
                <div class="invalid-feedback"><?= $errors['kode_kriteria'] ?? '' ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Kriteria</label>
                <input type="text" name="nama_kriteria" class="form-control <?= isset($errors['nama_kriteria']) ? 'is-invalid' : '' ?>" 
                    value="<?= old('nama_kriteria', kriteria_label($kriteria['nama_kriteria'] ?? '')) ?>" placeholder="Contoh: Nilai Rapor">
                <div class="invalid-feedback"><?= $errors['nama_kriteria'] ?? '' ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Bobot</label>
                <input type="number" step="0.0001" name="bobot" class="form-control <?= isset($errors['bobot']) ? 'is-invalid' : '' ?>" 
                    value="<?= old('bobot', $kriteria['bobot'] ?? '') ?>" placeholder="Contoh: 0.25">
                <div class="form-text">Total semua bobot kriteria harus bernilai 1.</div>
                <div class="invalid-feedback"><?= $errors['bobot'] ?? '' ?></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Jenis Atribut</label>
                <select name="jenis" class="form-select <?= isset($errors['jenis']) ? 'is-invalid' : '' ?>">
                    <option value="benefit" <?= (old('jenis', $kriteria['jenis'] ?? '') == 'benefit') ? 'selected' : '' ?>>Benefit (Makin Besar Makin Bagus)</option>
                    <option value="cost" <?= (old('jenis', $kriteria['jenis'] ?? '') == 'cost') ? 'selected' : '' ?>>Cost (Makin Kecil Makin Bagus)</option>
                </select>
                <div class="invalid-feedback"><?= $errors['jenis'] ?? '' ?></div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
