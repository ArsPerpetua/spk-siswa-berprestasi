<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="mb-4">
    <h3 class="fw-bold text-dark">Selamat Datang, <?= session()->get('nama_lengkap') ?>! 👋</h3>
    <p class="text-muted">Berikut adalah ringkasan data Sistem Pendukung Keputusan Siswa Berprestasi.</p>
</div>

<!-- ALERT STATUS AHP -->
<?php if (!$status_ahp): ?>
    <div class="alert alert-danger shadow-sm d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Perhatian: Bobot Kriteria Belum Valid!</h5>
            <p class="mb-0">Total bobot AHP saat ini tidak konsisten. Hasil perhitungan mungkin tidak akurat. Silakan
                lakukan pembobotan ulang.</p>
            <a href="<?= base_url('ahp') ?>" class="btn btn-sm btn-danger mt-2">Perbaiki Bobot AHP</a>
        </div>
    </div>
<?php endif; ?>

<!-- STATISTIK CARDS -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Total Siswa</div>
                <div class="h2 fw-bold text-dark mb-0"><?= $jumlah_alternatif ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Kriteria Penilaian</div>
                <div class="h2 fw-bold text-dark mb-0"><?= $jumlah_kriteria ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-info h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Siswa Dinilai</div>
                <div class="d-flex align-items-end justify-content-between">
                    <div class="h2 fw-bold text-dark mb-0"><?= $siswa_dinilai ?></div>
                    <span class="badge bg-info text-dark"><?= $persentase_penilaian ?>% Selesai</span>
                </div>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $persentase_penilaian ?>%">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">Pengguna Sistem</div>
                <div class="h2 fw-bold text-dark mb-0"><?= $jumlah_user ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- GRAFIK TOP 5 SISWA -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart-fill me-2"></i> Top 5 Siswa Terbaik
                    (Metode MOORA)</h6>
            </div>
            <div class="card-body">
                <canvas id="chartTopSiswa" height="150"></canvas>
                <?php if (empty(json_decode($grafik_nama))): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1"></i>
                        <p>Belum ada data penilaian yang cukup untuk menampilkan grafik.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SHORTCUTS / PINTASAN -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-secondary"><i class="bi bi-lightning-charge-fill me-2"></i> Akses Cepat</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="<?= base_url('alternatif/create') ?>" class="btn btn-outline-primary text-start p-3">
                        <i class="bi bi-person-plus-fill fs-4 me-3"></i>
                        <span class="fw-bold">Tambah Siswa Baru</span>
                    </a>
                    <a href="<?= base_url('penilaian') ?>" class="btn btn-outline-success text-start p-3">
                        <i class="bi bi-pencil-square fs-4 me-3"></i>
                        <span class="fw-bold">Input Penilaian</span>
                    </a>
                    <a href="<?= base_url('hitung') ?>" class="btn btn-outline-danger text-start p-3">
                        <i class="bi bi-calculator fs-4 me-3"></i>
                        <span class="fw-bold">Lihat Hasil Akhir</span>
                    </a>
                    <a href="<?= base_url('ahp') ?>" class="btn btn-outline-warning text-start p-3 text-dark">
                        <i class="bi bi-diagram-3-fill fs-4 me-3"></i>
                        <span class="fw-bold">Atur Bobot Kriteria</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    // Data dari Controller
    const labels = <?= $grafik_nama ?>; // Array Nama Siswa
    const ids = <?= $grafik_ids ?>; // Array ID Siswa
    const dataValues = <?= $grafik_moora ?>;
    const dataValuesAras = <?= $grafik_aras ?>;

    if (labels.length > 0) {
        const ctx = document.getElementById('chartTopSiswa').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels, // Nama Siswa
                datasets: [{
                    label: 'Nilai MOORA (Yi)',
                    data: dataValues,
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    //pointStyle: 'rectRot',
                    //  stack: 'Stack 1',
                },
                {
                    label: 'Nilai ARAS (Si)',
                    data: dataValuesAras,
                    backgroundColor: 'rgba(26, 188, 156, 0.7)',
                    borderColor: 'rgba(26, 188, 156, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    //   stack: 'Stack 2',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                onHover: (event, chartElement) => {
                    event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const studentId = ids[index];
                        window.location.href = '<?= base_url('penilaian/form') ?>/' + studentId;
                    }
                },
                plugins: {
                    title: {
                        display: false,
                        text: 'Perbandingan Nilai MOORA & ARAS'
                    },
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                }
            },
        });
    }
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>