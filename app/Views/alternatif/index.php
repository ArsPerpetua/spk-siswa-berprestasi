<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Data Siswa (Kandidat)</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import CSV
        </button>
        <a href="<?= base_url('alternatif/create') ?>" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah Siswa
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tableSiswa">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($alternatif as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['nis'] ?></td>
                        <td><?= $row['nama_siswa'] ?></td>
                        <td><?= $row['kelas'] ?></td>
                        <td>
                            <a href="<?= base_url('alternatif/edit/'.$row['id_alternatif']) ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <a href="<?= base_url('alternatif/delete/'.$row['id_alternatif']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus siswa ini?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= base_url('alternatif/import') ?>" method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Siswa (CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Format CSV:</strong><br>
                    Kolom 1: NIS<br>Kolom 2: Nama Siswa<br>Kolom 3: Kelas<br>
                    (Baris pertama dianggap header jika berisi teks 'NIS' atau 'Nama')
                </div>
                <div class="mb-3 text-end">
                    <a href="<?= base_url('alternatif/downloadTemplate') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download Template CSV</a>
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
        $('#tableSiswa').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });
    });
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>