<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-circle me-2"></i> Edit Profil Saya</h5>
            </div>
            <div class="card-body p-4">

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>

                <form action="<?= base_url('profile/update') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= $user['nama_lengkap'] ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= $user['username'] ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control"
                            placeholder="Kosongkan jika tidak ingin mengganti password">
                        <div class="form-text text-muted">Minimal 5 karakter jika ingin diganti.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Level Akses</label>
                        <input type="text" class="form-control bg-light" value="<?= ucfirst($user['level']) ?>"
                            readonly>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>