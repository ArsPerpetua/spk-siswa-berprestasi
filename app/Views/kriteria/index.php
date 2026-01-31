<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Data Kriteria</h4>
    <a href="<?= base_url('kriteria/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Kriteria
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tableKriteria">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode</th>
                        <th>Nama Kriteria</th>
                        <th width="15%">Bobot</th>
                        <th width="15%">Jenis</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($kriteria as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['kode_kriteria'] ?></td>
                            <td><?= $row['nama_kriteria'] ?></td>
                            <td><?= number_format($row['bobot'], 4) ?></td>
                            <td>
                                <?php if ($row['jenis'] == 'benefit'): ?>
                                    <span class="badge bg-success">Benefit</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Cost</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('kriteria/edit/' . $row['id_kriteria']) ?>"
                                    class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="<?= base_url('kriteria/delete/' . $row['id_kriteria']) ?>"
                                    class="btn btn-sm btn-danger" onclick="return confirm('Hapus kriteria ini?')"><i
                                        class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        $('#tableKriteria').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });
    });
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>