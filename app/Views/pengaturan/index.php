<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="container mt-4">

    <!-- FITUR BACKUP -->
    <div class="card shadow-sm border-primary mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-cloud-download-fill me-2"></i> Backup Database</h5>
        </div>
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="fw-bold text-dark">Amankan Data Anda</h5>
                <p class="text-muted mb-0">Unduh salinan data sistem (Siswa, Nilai, Kriteria, User) ke dalam file
                    <strong>.sql</strong>. <br>Lakukan ini secara berkala atau sebelum melakukan Reset Data.</p>
            </div>
            <a href="<?= base_url('pengaturan/backup') ?>" class="btn btn-primary btn-lg shadow">
                <i class="bi bi-download me-2"></i> Download Backup
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Reset Data Sistem</h5>
        </div>
        <div class="card-body text-center p-5">
            <div class="mb-4">
                <i class="bi bi-trash-fill text-danger" style="font-size: 4rem;"></i>
            </div>
            <h3 class="text-danger fw-bold mb-3">PERINGATAN: ZONA BERBAHAYA!</h3>
            <p class="lead mb-4 text-muted">
                Fitur ini akan menghapus <strong>SEMUA DATA SISWA</strong> dan <strong>DATA PENILAIAN</strong> secara
                permanen.<br>
                Data yang sudah dihapus tidak dapat dikembalikan lagi. Gunakan fitur ini hanya saat pergantian tahun
                ajaran baru.
            </p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('pengaturan/resetData') ?>" method="post"
                onsubmit="return confirm('APAKAH ANDA YAKIN? \n\nSemua data siswa dan nilai akan hilang selamanya. Tindakan ini tidak bisa dibatalkan!');">
                <button type="submit" class="btn btn-danger btn-lg px-5 rounded-pill shadow">
                    <i class="bi bi-arrow-counterclockwise me-2"></i> YA, HAPUS SEMUA DATA
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>