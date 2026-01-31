<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Siswa Berprestasi - SMA N 1 Utan</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --primary-color: #2563eb;
            /* Biru Profesional */
            --secondary-color: #1e293b;
            /* Gelap Elegant */
            --bg-color: #f3f4f6;
            /* Abu Lembut */
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-color);
            overflow-x: hidden;
            /* Mencegah scroll horizontal halaman */
        }

        /* --- SIDEBAR (Desktop First) --- */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #fff;
            z-index: 1050;
            transition: transform var(--transition-speed) ease;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.02);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-color);
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        .sidebar-menu {
            padding: 1.5rem 1rem;
            overflow-y: auto;
            /* Agar menu bisa discroll jika panjang */
            flex-grow: 1;
        }

        /* --- MENU ITEM STYLE --- */
        .nav-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
            font-weight: 700;
            margin: 1.5rem 0 0.5rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            color: #64748b;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
        }

        .nav-link:hover {
            background-color: #eff6ff;
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .nav-link i {
            font-size: 1.2rem;
            margin-right: 0.75rem;
        }

        /* --- MAIN CONTENT WRAPPER --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left var(--transition-speed) ease;
            display: flex;
            flex-direction: column;
        }

        /* --- HEADER / NAVBAR --- */
        .header {
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            /* Efek Kaca */
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content {
            padding: 2rem;
            flex-grow: 1;
        }

        /* --- RESPONSIVE LOGIC (MOBILE & TABLET) --- */
        /* Ketika layar kurang dari 992px (Laptop Kecil / Tablet / HP) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                /* Sembunyikan Sidebar ke Kiri */
            }

            .sidebar.show {
                transform: translateX(0);
                /* Munculkan saat class 'show' aktif */
            }

            .main-wrapper {
                margin-left: 0;
                /* Konten jadi full width */
            }

            .content {
                padding: 1rem;
                /* Padding lebih kecil di HP */
            }

            /* Overlay Gelap saat sidebar muncul di HP */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                /* Di bawah sidebar, di atas konten */
                display: none;
                opacity: 0;
                transition: opacity 0.3s;
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
        }

        /* --- TOMBOL BURGER (Hanya muncul di Mobile) --- */
        .btn-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--secondary-color);
            cursor: pointer;
            padding: 0;
            display: none;
            /* Sembunyi di desktop */
        }

        @media (max-width: 991.98px) {
            .btn-toggle {
                display: block;
            }
        }

        /* --- STYLE TAMBAHAN --- */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Agar tabel di HP bisa discroll horizontal */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>

<body>

    <div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill me-2"></i> SPK SISWA
            <button class="btn d-lg-none ms-auto text-muted" onclick="toggleSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= uri_string() == 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid"></i> Dashboard
            </a>

            <div class="nav-label">Data Master</div>

            <a href="<?= base_url('kriteria') ?>"
                class="nav-link <?= (uri_string() == 'kriteria' || strpos(uri_string(), 'kriteria/') === 0) ? 'active' : '' ?>">
                <i class="bi bi-list-check"></i> Data Kriteria
            </a>

            <a href="<?= base_url('ahp') ?>" class="nav-link <?= (uri_string() == 'ahp') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i> Pembobotan AHP
            </a>

            <a href="<?= base_url('alternatif') ?>"
                class="nav-link <?= (uri_string() == 'alternatif' || strpos(uri_string(), 'alternatif/') === 0) ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Data Siswa
            </a>

            <div class="nav-label">Proses & Hasil</div>

            <a href="<?= base_url('penilaian') ?>"
                class="nav-link <?= (uri_string() == 'penilaian') ? 'active' : '' ?>">
                <i class="bi bi-pencil-square"></i> Input Penilaian
            </a>

            <a href="<?= base_url('hitung') ?>" class="nav-link <?= (uri_string() == 'hitung') ? 'active' : '' ?>">
                <i class="bi bi-calculator"></i> Hasil & Komparasi
            </a>

            <div class="nav-label">Pengguna</div>

            <a href="<?= base_url('profile') ?>" class="nav-link <?= (uri_string() == 'profile') ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i> Profil Saya
            </a>

            <?php if (session()->get('level') == 'admin'): ?>
                <a href="<?= base_url('users') ?>" class="nav-link <?= (uri_string() == 'users') ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i> Manajemen User
                </a>
            <?php endif; ?>
            <div class="nav-label">Lainnya</div>

            <?php if (session()->get('level') == 'admin'): ?>
                <a href="<?= base_url('pengaturan') ?>"
                    class="nav-link <?= (uri_string() == 'pengaturan') ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i> Pengaturan
                </a>
            <?php endif; ?>

            <a href="<?= base_url('panduan') ?>" class="nav-link <?= (uri_string() == 'panduan') ? 'active' : '' ?>">
                <i class="bi bi-book-half"></i> Panduan Penggunaan
            </a>

            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#aboutModal">
                <i class="bi bi-info-square"></i> Tentang Aplikasi
            </a>
        </div>

        <div class="p-3 border-top">
            <a href="<?= base_url('auth/logout') ?>"
                class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
        </div>
    </nav>

    <div class="main-wrapper">

        <header class="header">
            <button class="btn-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div class="fw-bold text-secondary d-none d-md-block">
                Sistem Pendukung Keputusan MOORA & ARAS
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold small text-dark"><?= session()->get('nama_lengkap') ?></div>
                    <div class="text-muted small" style="font-size: 0.7rem;"><?= ucfirst(session()->get('level')) ?>
                    </div>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('nama_lengkap')) ?>&background=2563eb&color=fff"
                    class="rounded-circle" width="40" height="40">
            </div>
        </header>

        <div class="content">
            <?= $this->renderSection('content') ?>
        </div>

        <footer class="text-center py-4 text-muted small">
            &copy; 2026 SMA Negeri 1 Utan - Skripsi Helda Nur Afidah
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Fungsi untuk Buka/Tutup Sidebar di Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Inisialisasi Tooltip Bootstrap 5
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    <div class="modal fade" id="aboutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-info-circle-fill me-2"></i> Tentang Sistem</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Sistem Pendukung Keputusan (SPK)</h5>
                    <p class="text-muted small mb-3">Pemilihan Siswa Berprestasi Metode MOORA & ARAS</p>

                    <div class="p-3 bg-light rounded border mb-3">
                        <table class="table table-sm table-borderless mb-0 text-start">
                            <tr>
                                <td class="text-muted" width="80">Pengembang</td>
                                <td class="fw-bold">: Helda Nur Afidah</td>
                            </tr>
                            <tr>
                                <td class="text-muted">NIM</td>
                                <td class="fw-bold">: 2101010027</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Prodi</td>
                                <td class="fw-bold">: S1 Ilmu Komputer</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kampus</td>
                                <td class="fw-bold">: Universitas Bumigora</td>
                            </tr>
                        </table>
                    </div>

                    <p class="small text-muted mb-0">
                        Dibuat sebagai syarat kelulusan Sarjana Komputer.<br>
                        &copy; 2026 - Versi 1.0
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <?= $this->renderSection('scripts') ?>
</body>

</html>