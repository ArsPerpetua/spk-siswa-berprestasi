<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white">
        <h5 class="mb-0"><?= $title ?></h5>
    </div>
    <div class="card-body">
        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul><?php foreach(session()->getFlashdata('errors') as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="<?= isset($alternatif) ? base_url('alternatif/update/'.$alternatif['id_alternatif']) : base_url('alternatif/store') ?>" method="post">
            
            <div class="mb-3">
                <label>Nomor Induk Siswa (NIS)</label>
                <input type="number" name="nis" class="form-control" value="<?= $alternatif['nis'] ?? old('nis') ?>" required placeholder="Contoh: 21001">
            </div>

            <div class="mb-3">
                <label>Nama Lengkap Siswa</label>
                <input type="text" name="nama_siswa" class="form-control" value="<?= $alternatif['nama_siswa'] ?? old('nama_siswa') ?>" required placeholder="Nama Siswa">
            </div>

            <div class="mb-3">
                <label>Kelas</label>
                <input type="text" name="kelas" class="form-control" value="<?= $alternatif['kelas'] ?? old('kelas') ?>" required placeholder="Contoh: XII IPA 1">
            </div>

            <a href="<?= base_url('alternatif') ?>" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Data</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>