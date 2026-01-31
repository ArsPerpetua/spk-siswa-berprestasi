<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Hasil Perhitungan AHP</h4>
    <a href="<?= base_url('ahp') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<!-- Alert Konsistensi -->
<div class="alert <?= $is_consistent ? 'alert-success' : 'alert-danger' ?> shadow-sm">
    <h5 class="alert-heading fw-bold"><i class="bi <?= $is_consistent ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i> <?= $is_consistent ? 'Konsisten' : 'Tidak Konsisten' ?></h5>
    <p class="mb-0"><?= $pesan ?></p>
</div>

<div class="row">
    <!-- Tabel Bobot Prioritas -->
    <div class="col-md-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-table me-2"></i> Bobot Prioritas Kriteria</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Bobot (Eigen Vector)</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kriteria as $k): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $k['kode_kriteria'] ?></td>
                                    <td><?= $k['nama_kriteria'] ?></td>
                                    <td class="text-center"><?= number_format($bobot[$k['id_kriteria']], 4) ?></td>
                                    <td class="text-center fw-bold text-primary">
                                        <?= number_format($bobot[$k['id_kriteria']] * 100, 2) ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Total</td>
                                <td class="text-center"><?= number_format(array_sum($bobot), 4) ?></td>
                                <td class="text-center">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Pie Chart -->
    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i> Visualisasi Bobot</h6>
            </div>
            <div class="card-body">
                <canvas id="chartBobot"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Matriks Perbandingan (Opsional/Detail) -->
<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">
        <h6 class="mb-0"><i class="bi bi-grid-3x3 me-2"></i> Matriks Perbandingan Berpasangan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center">
                <thead class="table-light">
                    <tr>
                        <th>Kriteria</th>
                        <?php foreach ($kriteria as $k): ?>
                            <th><?= $k['kode_kriteria'] ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kriteria as $row): ?>
                        <tr>
                            <th class="table-light"><?= $row['kode_kriteria'] ?></th>
                            <?php foreach ($kriteria as $col): ?>
                                <td><?= number_format($matriks[$row['id_kriteria']][$col['id_kriteria']], 4) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    // Inisialisasi Chart.js
    const ctx = document.getElementById('chartBobot').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= $chart_labels ?>, // Dari Controller
            datasets: [{
                data: <?= $chart_data ?>, // Dari Controller
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>