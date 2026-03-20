# SPK Siswa Berprestasi (MOORA & ARAS)

Sistem Pendukung Keputusan untuk pemilihan siswa berprestasi di sekolah. Aplikasi ini membantu tim penilai menyusun kriteria, melakukan pembobotan AHP, memberi nilai siswa, lalu membandingkan hasil ranking dengan metode MOORA dan ARAS. 

Klien: Mahasiswa akhir Universitas Bumigora (Tugas Akhir/Skripsi)  
Peran saya: Freelance Full-Stack Developer

## Ringkasan Fitur
- Manajemen kriteria (benefit/cost) dan bobot penilaian.
- Pembobotan AHP dengan validasi konsistensi.
- Manajemen data siswa (CRUD) + import CSV + template CSV.
- Input penilaian per siswa + import nilai via CSV.
- Perhitungan MOORA dan ARAS lengkap dengan tabel normalisasi dan ranking.
- Komparasi hasil akhir MOORA vs ARAS.
- Laporan PDF dan pengaturan backup database (.sql) serta reset data tahunan.

## Metodologi
- AHP untuk pembobotan kriteria.
- MOORA dan ARAS untuk perankingan alternatif (siswa).

## Tech Stack
- Backend: PHP 8.1, CodeIgniter 4
- Frontend: Bootstrap 5, Bootstrap Icons, DataTables, jQuery
- Reporting: Dompdf

## Struktur Modul Utama
- Dashboard
- Data Kriteria
- Pembobotan AHP
- Data Siswa
- Input Penilaian
- Hasil & Komparasi
- Pengaturan (backup/reset)
- Panduan Penggunaan

## Instalasi Lokal (Ringkas)
1. `composer install`
2. Salin `env` menjadi `.env`, lalu set `baseURL` dan kredensial database.
3. Arahkan document root web server ke folder `public/`.
4. Jalankan aplikasi (mis. `php spark serve`) dan buka di browser.

Catatan: Struktur database disesuaikan dengan tabel `users`, `kriteria`, `alternatif`, `penilaian`, dan `presets`.

## Deploy ke Railway
Project ini bisa dideploy ke Railway dari GitHub dengan `railway.toml` di root repo.

Environment variables minimum yang perlu diisi di Railway:
- `CI_ENVIRONMENT=production`
- `app.baseURL=https://<domain-railway-anda>/`
- `database.default.hostname=<host-mysql>`
- `database.default.database=<nama-db>`
- `database.default.username=<user-db>`
- `database.default.password=<password-db>`
- `database.default.DBDriver=MySQLi`
- `database.default.port=3306`

Start command sudah disediakan lewat `railway.toml`:
- `php spark serve --host 0.0.0.0 --port $PORT`

Catatan deploy:
- Tambahkan service MySQL di Railway atau gunakan MySQL eksternal.
- Folder `writable/` dipakai untuk session/log/cache. Di Railway filesystem bersifat ephemeral, jadi untuk penggunaan yang lebih stabil pertimbangkan Volume atau pindahkan session ke database/Redis.
- Pastikan akun admin dibuat lewat database atau seeder, bukan route publik.

## Catatan Pengerjaan
Proyek ini dikerjakan secara end-to-end sebagai freelance, mencakup analisis kebutuhan, perancangan UI, implementasi backend, dan pengujian fungsional.
