<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-header bg-white">
        <h5 class="mb-0"><?= $title ?></h5>
    </div>
    <div class="card-body">
        
        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= isset($user) ? base_url('users/update/'.$user['id_user']) : base_url('users/store') ?>" method="post">
            
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" value="<?= $user['nama_lengkap'] ?? old('nama_lengkap') ?>" required>
            </div>

            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= $user['username'] ?? old('username') ?>" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="<?= isset($user) ? 'Kosongkan jika tidak ingin mengganti password' : 'Minimal 5 karakter' ?>">
                <?php if(isset($user)): ?>
                    <small class="text-muted">* Biarkan kosong jika password tidak diganti.</small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label>Level Akses</label>
                <select name="level" class="form-select">
                    <?php $currentLevel = strtolower(trim((string) ($user['level'] ?? old('level') ?? 'siswa'))); ?>
                    <option value="admin" <?= ($currentLevel === 'admin') ? 'selected' : '' ?>>Administrator</option>
                    <option value="siswa" <?= ($currentLevel !== 'admin') ? 'selected' : '' ?>>Siswa</option>
                </select>
            </div>

            <a href="<?= base_url('users') ?>" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
