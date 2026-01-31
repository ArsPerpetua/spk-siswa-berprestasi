<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Manajemen User</h4>
    <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Tambah User
    </a>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach($users as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['nama_lengkap'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td>
                        <span class="badge bg-<?= ($row['level']=='admin')?'danger':'secondary' ?>">
                            <?= ucfirst($row['level']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= base_url('users/edit/'.$row['id_user']) ?>" class="btn btn-sm btn-warning">Edit</a>
                        
                        <?php if(session()->get('id_user') != $row['id_user']): ?>
                            <a href="<?= base_url('users/delete/'.$row['id_user']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>