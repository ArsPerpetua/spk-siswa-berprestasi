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
                <input type="text" name="nama_siswa" id="nama_siswa" class="form-control text-uppercase"
                    value="<?= esc($alternatif['nama_siswa'] ?? old('nama_siswa')) ?>" required
                    placeholder="NAMA SISWA" autocomplete="name">
                <div class="form-text">Nama otomatis diubah menjadi huruf kapital.</div>
            </div>

            <div class="mb-3">
                <label>Kelas</label>
                <?php $selectedKelas = (string) ($alternatif['kelas'] ?? old('kelas')); ?>
                <select name="kelas" class="form-select" required>
                    <option value="" <?= $selectedKelas === '' ? 'selected' : '' ?> disabled>-- Pilih Kelas --</option>
                    <?php foreach (($kelas_options ?? []) as $kelas): ?>
                        <option value="<?= esc($kelas) ?>" <?= $selectedKelas === $kelas ? 'selected' : '' ?>>
                            <?= esc($kelas) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <a href="<?= base_url('alternatif') ?>" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Data</button>
        </form>
    </div>
</div>
<script>
document.getElementById('nama_siswa')?.addEventListener('input', function () {
    const start = this.selectionStart;
    const end = this.selectionEnd;
    this.value = this.value.toLocaleUpperCase('id-ID');
    this.setSelectionRange(start, end);
});
</script>
<?= $this->endSection() ?>
