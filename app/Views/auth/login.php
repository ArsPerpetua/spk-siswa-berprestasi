<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SPK Siswa Berprestasi</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
        }

        body {
            font-family: 'Nunito', sans-serif;
            height: 100vh;
            overflow: hidden;
            /* Mencegah scroll bar ganda */
            background-color: #fff;
        }

        .main-container {
            height: 100vh;
        }

        /* --- BAGIAN KIRI (BRANDING) --- */
        .left-side {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }

        /* Hiasan Lingkaran Transparan */
        .circle-decoration {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -50px;
        }

        .circle-2 {
            width: 500px;
            height: 500px;
            bottom: -100px;
            right: -100px;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .brand-desc {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 600px;
            position: relative;
            z-index: 2;
        }

        /* --- BAGIAN KANAN (FORM) --- */
        .right-side {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            position: relative;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            animation: fadeIn 0.8s ease;
        }

        /* --- UPDATE CSS FORM --- */
        .form-control {
            padding: 0.8rem 1rem;
            padding-right: 3rem;
            /* Tambah padding kanan agar teks tidak menabrak ikon */
            border-radius: 0.75rem;
            border: 2px solid #f1f5f9;
            background-color: #f8fafc;
            font-weight: 600;
            transition: all 0.3s;
            height: 50px;
            /* Kita kunci tingginya agar konsisten */
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        /* Class Baru untuk Ikon agar presisi */
        .form-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            /* Trik jitu agar pas di tengah vertikal */
            color: #94a3b8;
            font-size: 1.2rem;
            pointer-events: none;
            /* Agar ikon tidak menghalangi saat diklik */
            transition: color 0.3s;
        }

        /* Efek saat input difokuskan, ikon jadi biru */
        .form-control:focus+.form-icon {
            color: var(--primary-color);
        }

        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        .input-group-text {
            border: none;
            background: none;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 5;
            cursor: pointer;
        }

        /* --- RESPONSIF (MOBILE & TABLET) --- */
        @media (max-width: 991.98px) {

            /* Sembunyikan Panel Kiri di Mobile */
            .left-side {
                display: none;
            }

            /* Panel Kanan Penuh Layar */
            .right-side {
                width: 100%;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                /* Background beda dikit biar ga polos */
            }

            .login-card {
                background: white;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
                padding: 3rem 2rem;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid g-0 main-container">
        <div class="row g-0 h-100">

            <div class="col-lg-7 left-side">
                <div class="circle-decoration circle-1"></div>
                <div class="circle-decoration circle-2"></div>

                <div class="mb-4">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
                        <i class="bi bi-mortarboard-fill me-1"></i> SPK V.1.0
                    </span>
                </div>

                <h1 class="brand-title">Sistem Pendukung Keputusan<br>Siswa Berprestasi</h1>

                <p class="brand-desc">
                    Menggunakan metode <strong>MOORA</strong> dan <strong>ARAS</strong> berbasis pembobotan
                    <strong>AHP</strong> untuk hasil seleksi yang objektif, transparan, dan akurat.
                    <br><br>
                    Studi Kasus: <strong>SMA Negeri 1 Utan</strong>
                </p>

                <div class="mt-5 text-white-50 small">
                    &copy; 2026 Helda Nur Afidah - Universitas Bumigora
                </div>
            </div>

            <div class="col-lg-5 col-md-12 right-side">
                <div class="login-card">

                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-dark">Selamat Datang 👋</h2>
                        <p class="text-muted">Silakan login untuk mengakses sistem</p>
                    </div>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= session()->getFlashdata('error') ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('auth/login') ?>" method="post">

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold ms-1">Username</label>
                            <div class="position-relative">
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username"
                                    required autocomplete="off">
                                <i class="bi bi-person form-icon"></i>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold ms-1">Password</label>
                            <div class="position-relative">
                                <input type="password" name="password" class="form-control"
                                    placeholder="Masukkan password" required>
                                <i class="bi bi-lock form-icon"></i>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login shadow-sm">
                                Masuk Aplikasi <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>

                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">Lupa password? Hubungi Administrator.</small>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>

</html>