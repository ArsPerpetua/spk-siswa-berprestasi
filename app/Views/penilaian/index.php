<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Input Penilaian Siswa</h4>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importNilaiModal">
        <i class="bi bi-file-earmark-spreadsheet"></i> Import Nilai (CSV)
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tablePenilaian">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">NIS</th>
                        <th>Nama Siswa</th>
                        <th width="10%">Kelas</th>
                        <th width="15%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($alternatif as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['nis'] ?></td>
                            <td><?= $row['nama_siswa'] ?></td>
                            <td><?= $row['kelas'] ?></td>
                            <td class="text-center">
                                <?php if (isset($data_penilaian[$row['id_alternatif']]) && $data_penilaian[$row['id_alternatif']] === true): ?>
                                    <span class="badge bg-success">Sudah Dinilai</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Belum Dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('penilaian/form/' . $row['id_alternatif']) ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i> Input Nilai
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importNilaiModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= base_url('penilaian/import') ?>" method="post" enctype="multipart/form-data"
            class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Penilaian (CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Petunjuk:</strong><br>
                    1. Download template terlebih dahulu.<br>
                    2. Isi nilai pada kolom kriteria (C1, C2, dst).<br>
                    3. Jangan ubah NIS agar data sinkron.
                </div>
                <div class="mb-3 text-end">
                    <a href="<?= base_url('penilaian/downloadTemplate') ?>" class="btn btn-sm btn-outline-primary"><i
                            class="bi bi-download"></i> Download Template CSV</a>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pilih File CSV</label>
                    <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tablePenilaian').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });
    });
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>