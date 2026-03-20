<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $isSiswa = strtolower((string) session()->get('level')) === 'siswa'; ?>

<div class="text-center mb-5">
    <h2 class="fw-bold text-primary">
        <?= $isSiswa ? 'Panduan Penggunaan untuk Siswa' : 'Panduan Penggunaan Sistem' ?>
    </h2>
    <p class="text-muted">
        <?= $isSiswa
            ? 'Ikuti langkah singkat berikut untuk melihat posisi ranking dan memahami hasil perhitungan.'
            : 'Ikuti langkah-langkah berikut untuk mendapatkan hasil rekomendasi siswa berprestasi.' ?>
    </p>
</div>

<?php if ($isSiswa): ?>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-3 text-primary">1</span>
                    </div>
                    <h5 class="fw-bold">Cek Profil</h5>
                    <p class="text-muted small">Pastikan nama dan username akun sudah benar agar data siswa Anda terpetakan dengan tepat.</p>
                    <a href="<?= base_url('profile') ?>" class="btn btn-sm btn-outline-primary">Ke Profil <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-3 text-primary">2</span>
                    </div>
                    <h5 class="fw-bold">Lihat Ranking Saya</h5>
                    <p class="text-muted small">Buka menu <strong>Lihat Ranking Saya</strong> untuk melihat posisi Anda pada metode MOORA dan ARAS.</p>
                    <a href="<?= base_url('siswa/ranking') ?>" class="btn btn-sm btn-outline-primary">Ke Ranking Saya <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-3 text-primary">3</span>
                    </div>
                    <h5 class="fw-bold">Coba Simulasi</h5>
                    <p class="text-muted small">Gunakan simulasi nilai di halaman ranking untuk memahami pengaruh perubahan nilai terhadap posisi Anda.</p>
                    <a href="<?= base_url('siswa/ranking') ?>" class="btn btn-sm btn-outline-primary">Buka Simulasi <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-primary">
                <div class="card-body text-center p-4">
                    <div class="bg-primary text-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-bar-chart-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold">Lihat Komparasi Umum</h5>
                    <p class="text-muted small">Buka menu <strong>Hasil & Komparasi</strong> untuk melihat gambaran ranking keseluruhan siswa.</p>
                    <a href="<?= base_url('hitung') ?>" class="btn btn-sm btn-primary">Ke Komparasi <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-5 d-flex align-items-start shadow-sm">
        <i class="bi bi-info-circle-fill fs-3 me-3"></i>
        <div>
            <h5 class="fw-bold mb-1">Catatan untuk Siswa</h5>
            <ul class="mb-0 small">
                <li>Jika data Anda tidak muncul, pastikan username akun sesuai NIS.</li>
                <li>Posisi ranking dapat berubah jika data penilaian diperbarui admin.</li>
                <li>Gunakan fitur simulasi untuk memahami strategi peningkatan nilai.</li>
            </ul>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-3 text-primary">1</span>
                    </div>
                    <h5 class="fw-bold">Atur Kriteria</h5>
                    <p class="text-muted small">Masuk ke menu <strong>Data Kriteria</strong>. Pastikan kriteria penilaian (seperti Nilai Rapor, Absensi, dll) sudah sesuai.</p>
                    <a href="<?= base_url('kriteria') ?>" class="btn btn-sm btn-outline-primary">Ke Kriteria <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-3 text-primary">2</span>
                    </div>
                    <h5 class="fw-bold">Hitung Bobot (AHP)</h5>
                    <p class="text-muted small">Masuk ke menu <strong>Pembobotan AHP</strong>. Bandingkan kepentingan antar kriteria untuk mendapatkan bobot yang valid.</p>
                    <a href="<?= base_url('ahp') ?>" class="btn btn-sm btn-outline-primary">Ke AHP <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="bg-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="fw-bold fs-3 text-primary">3</span>
                    </div>
                    <h5 class="fw-bold">Input Data & Nilai</h5>
                    <p class="text-muted small">Masukkan data siswa di menu <strong>Data Siswa</strong>, lalu isi nilai mereka di menu <strong>Input Penilaian</strong> (bisa import).</p>
                    <a href="<?= base_url('penilaian') ?>" class="btn btn-sm btn-outline-primary">Ke Penilaian <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 shadow-sm border-primary">
                <div class="card-body text-center p-4">
                    <div class="bg-primary text-white rounded-circle shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="bi bi-trophy-fill fs-4"></i>
                    </div>
                    <h5 class="fw-bold">Lihat Hasil</h5>
                    <p class="text-muted small">Masuk ke menu <strong>Hasil & Komparasi</strong>. Sistem menghitung ranking siswa terbaik dengan metode MOORA & ARAS.</p>
                    <a href="<?= base_url('hitung') ?>" class="btn btn-sm btn-primary">Lihat Ranking <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-5 d-flex align-items-center shadow-sm">
        <i class="bi bi-info-circle-fill fs-3 me-3"></i>
        <div>
            <h5 class="fw-bold mb-1">Tips Tambahan</h5>
            <ul class="mb-0 small">
                <li>Gunakan fitur import jika data siswa sangat banyak.</li>
                <li>Jika hasil ranking terasa tidak sesuai, cek kembali bobot AHP dan konsistensi.</li>
                <li>Lakukan backup data di menu Pengaturan secara berkala.</li>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="text-center mt-4">
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="bi bi-house-door-fill me-2"></i> Kembali ke Dashboard
    </a>
</div>

<?= $this->endSection() ?>
