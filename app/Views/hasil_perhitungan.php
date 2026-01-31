<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i> Detail Perhitungan & Hasil</h5>
        <a href="<?= base_url('hitung/cetakPDF') ?>" target="_blank" class="btn btn-sm btn-light text-danger fw-bold">
            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Download Laporan PDF
        </a>
    </div>
    <div class="card-body">

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-warning"><?= $error_msg ?></div>
        <?php else: ?>

            <ul class="nav nav-tabs nav-fill fw-bold" id="myTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="data-tab" data-bs-toggle="tab" data-bs-target="#data"
                        type="button">1. Matriks Awal</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="moora-tab" data-bs-toggle="tab" data-bs-target="#moora" type="button">2.
                        Detail MOORA</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="aras-tab" data-bs-toggle="tab" data-bs-target="#aras" type="button">3.
                        Detail ARAS</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link text-primary" id="komparasi-tab" data-bs-toggle="tab"
                        data-bs-target="#komparasi" type="button"><i class="bi bi-trophy me-1"></i> KOMPARASI AKHIR</button>
                </li>
            </ul>

            <div class="tab-content mt-4" id="myTabContent">

                <div class="tab-pane fade show active" id="data">
                    <div class="alert alert-primary d-flex align-items-center shadow-sm">
                        <i class="bi bi-database-fill fs-4 me-3"></i>
                        <div>
                            <strong>Data Mentah (Matriks Keputusan)</strong>
                            <br><small>Ini adalah nilai asli siswa yang diambil dari database. Kolom dengan label <span
                                    class="text-warning fw-bold">Benefit</span> berarti nilai makin besar makin bagus
                                (contoh: Nilai Rapor). Label <span class="text-warning fw-bold">Cost</span> berarti nilai
                                makin kecil makin bagus (contoh: Penghasilan Ortu).</small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover w-100" id="tableMatriks">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="<?= $k['nama_kriteria'] ?> (<?= ucfirst($k['jenis']) ?>)">
                                            <?= $k['kode_kriteria'] ?><br><small
                                                class="text-warning"><?= ucfirst($k['jenis']) ?></small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatif as $a): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $a['nis'] ?></td>
                                        <td><?= $a['nama_siswa'] ?></td>
                                        <?php foreach ($kriteria as $k): ?>
                                            <td class="text-center"><?= $matriks[$a['id_alternatif']][$k['id_kriteria']] ?? 0 ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="moora">

                    <h6 class="fw-bold text-primary mt-3"><i class="bi bi-1-circle-fill me-2"></i>Tahap 1: Normalisasi
                        Matriks (X*)</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm w-100">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Nama Siswa</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="<?= $k['nama_kriteria'] ?> (<?= ucfirst($k['jenis']) ?>)">
                                            <?= $k['kode_kriteria'] ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alternatif as $a): ?>
                                    <tr>
                                        <td><?= $a['nama_siswa'] ?></td>
                                        <?php foreach ($kriteria as $k): ?>
                                            <td class="text-center text-muted small">
                                                <?= number_format($moora_normalisasi[$a['id_alternatif']][$k['id_kriteria']], 4) ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold text-primary"><i class="bi bi-2-circle-fill me-2"></i>Tahap 2: Nilai Optimasi &
                        Ranking (Yi)</h6>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i> <strong>Penjelasan:</strong> Sistem menjumlahkan semua nilai
                        kriteria yang menguntungkan (Benefit), lalu dikurangi dengan kriteria yang merugikan (Cost). <br>
                        Rumus: <strong>Yi = (Total Benefit) - (Total Cost)</strong>. Semakin besar nilai Yi, semakin layak
                        siswa tersebut.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered w-100" id="tableRankMoora">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th width="5%">Rank</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Total Benefit (Max)</th>
                                    <th>Total Cost (Min)</th>
                                    <th>Nilai Yi (Akhir)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1;
                                foreach ($hasil_moora as $row): ?>
                                    <tr>
                                        <td class="fw-bold text-center h5"><?= $rank++ ?></td>
                                        <td class="text-center"><?= $row['nis'] ?></td>
                                        <td><?= $row['nama'] ?></td>
                                        <td class="text-center text-success"><?= number_format($row['max'], 4) ?></td>
                                        <td class="text-center text-danger"><?= number_format($row['min'], 4) ?></td>
                                        <td class="fw-bold text-end pe-4 bg-primary bg-opacity-10">
                                            <?= number_format($row['nilai'], 4) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="aras">

                    <h6 class="fw-bold text-success mt-3"><i class="bi bi-1-circle-fill me-2"></i>Tahap 1: Matriks
                        Normalisasi (Rij)</h6>
                    <div class="alert alert-light border py-2 small">
                        <i class="bi bi-info-circle me-1"></i> <strong>Konsep ARAS:</strong> Metode ini membayangkan adanya
                        satu <strong>"Siswa Ideal (A0)"</strong> yang memiliki nilai sempurna di semua mata pelajaran. <br>
                        Semua siswa lain akan dibandingkan kemampuannya terhadap Siswa Ideal tersebut.
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm w-100">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Nama Siswa</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="<?= $k['nama_kriteria'] ?> (<?= ucfirst($k['jenis']) ?>)">
                                            <?= $k['kode_kriteria'] ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-warning fw-bold">
                                    <td>A0 (Ideal)</td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <td class="text-center"><?= number_format($aras_normalisasi[0][$k['id_kriteria']], 4) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php foreach ($alternatif as $a): ?>
                                    <tr>
                                        <td><?= $a['nama_siswa'] ?></td>
                                        <?php foreach ($kriteria as $k): ?>
                                            <td class="text-center text-muted small">
                                                <?= number_format($aras_normalisasi[$a['id_alternatif']][$k['id_kriteria']], 4) ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold text-success"><i class="bi bi-2-circle-fill me-2"></i>Tahap 2: Matriks Terbobot (Dij)
                    </h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm w-100">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Nama Siswa</th>
                                    <?php foreach ($kriteria as $k): ?>
                                        <th data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="<?= $k['nama_kriteria'] ?> (<?= ucfirst($k['jenis']) ?>)">
                                            <?= $k['kode_kriteria'] ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-warning fw-bold">
                                    <td>A0 (Ideal)</td>
                                    <?php foreach ($kriteria as $k): ?>
                                        <td class="text-center"><?= number_format($aras_terbobot[0][$k['id_kriteria']], 4) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php foreach ($alternatif as $a): ?>
                                    <tr>
                                        <td><?= $a['nama_siswa'] ?></td>
                                        <?php foreach ($kriteria as $k): ?>
                                            <td class="text-center text-muted small">
                                                <?= number_format($aras_terbobot[$a['id_alternatif']][$k['id_kriteria']], 4) ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold text-success"><i class="bi bi-3-circle-fill me-2"></i>Tahap 3: Utilitas & Ranking
                        (Ki)</h6>
                    <div class="alert alert-success py-2 small">
                        <i class="bi bi-check-circle me-1"></i> <strong>Hasil Akhir:</strong> Nilai Utilitas (Ki)
                        menunjukkan seberapa mirip siswa tersebut dengan Siswa Ideal. <br>
                        Nilai <strong>1.00</strong> berarti sempurna (sama dengan ideal). Semakin mendekati 1, semakin baik.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered w-100" id="tableRankAras">
                            <thead class="table-success text-center">
                                <tr>
                                    <th width="5%">Rank</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Nilai Optimalitas (Si)</th>
                                    <th>Nilai Utilitas (Ki)</th>
                                    <th>Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1;
                                foreach ($hasil_aras as $row): ?>
                                    <tr>
                                        <td class="fw-bold text-center h5"><?= $rank++ ?></td>
                                        <td class="text-center"><?= $row['nis'] ?></td>
                                        <td><?= $row['nama'] ?></td>
                                        <td class="text-center"><?= number_format($row['Si'], 4) ?></td>
                                        <td class="fw-bold text-center bg-success bg-opacity-10">
                                            <?= number_format($row['nilai'], 4) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $badge = 'bg-secondary';
                                            if($row['predikat'] == 'Sangat Baik') $badge = 'bg-success';
                                            elseif($row['predikat'] == 'Baik') $badge = 'bg-primary';
                                            elseif($row['predikat'] == 'Cukup') $badge = 'bg-info text-dark';
                                            elseif($row['predikat'] == 'Kurang') $badge = 'bg-warning text-dark';
                                            elseif($row['predikat'] == 'Sangat Kurang') $badge = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $row['predikat'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="komparasi">
                    <?php
                    $top_moora = $hasil_moora[0] ?? null;
                    $top_aras = $hasil_aras[0] ?? null;
                    ?>

                    <!-- KESIMPULAN AKHIR (UNTUK ORANG AWAM) -->
                    <div class="card border-primary mb-4 shadow-sm">
                        <div class="card-body bg-light">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 50px; height: 50px;">
                                        <i class="bi bi-lightbulb-fill fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-primary">Kesimpulan & Rekomendasi Keputusan</h5>
                                    <p class="mb-2">
                                        Berdasarkan hasil perhitungan sistem menggunakan dua metode (MOORA & ARAS), siswa
                                        yang paling direkomendasikan adalah:
                                    </p>
                                    <div class="alert alert-white border shadow-sm">
                                        <?php if ($top_moora && $top_aras): ?>
                                            <?php if ($top_moora['nis'] == $top_aras['nis']): ?>
                                                <h4 class="fw-bold text-success mb-1"><i class="bi bi-trophy-fill text-warning"></i>
                                                    <?= $top_moora['nama'] ?> (NIS: <?= $top_moora['nis'] ?>)</h4>
                                                <small class="text-muted">Siswa ini menempati <strong>Peringkat 1</strong> di kedua
                                                    metode perhitungan. Hasil sangat kuat dan konsisten.</small>
                                            <?php else: ?>
                                                <h5 class="fw-bold text-dark mb-1">Terdapat Perbedaan Rekomendasi Utama:</h5>
                                                <ul class="mb-0">
                                                    <li>Metode MOORA menyarankan: <strong><?= $top_moora['nama'] ?></strong></li>
                                                    <li>Metode ARAS menyarankan: <strong><?= $top_aras['nama'] ?></strong></li>
                                                </ul>
                                                <small class="text-muted">Silakan lihat detail nilai di bawah untuk pertimbangan
                                                    lebih lanjut.</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Data belum cukup untuk memberikan kesimpulan.</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <strong>Cara Membaca Tabel:</strong> Fokus pada kolom <strong>"Rank"</strong>. Angka
                                        <strong>1</strong> berarti terbaik.
                                        Jika kolom "Status" berwarna hijau, artinya kedua metode sepakat mengenai posisi
                                        siswa tersebut.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PENJELASAN PERBEDAAN (ACCORDION) -->
                    <div class="accordion mb-4 shadow-sm" id="accordionInfo">
                        <div class="accordion-item border-warning">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-warning bg-opacity-10 text-dark fw-bold"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhy">
                                    <i class="bi bi-question-circle-fill me-2"></i> Mengapa Hasil Ranking Bisa Berbeda?
                                </button>
                            </h2>
                            <div id="collapseWhy" class="accordion-collapse collapse" data-bs-parent="#accordionInfo">
                                <div class="accordion-body bg-white text-muted small">
                                    <p>Jangan bingung jika peringkat siswa berbeda antara metode MOORA dan ARAS. Ini terjadi
                                        karena cara hitung matematikanya berbeda:</p>
                                    <ul class="mb-0">
                                        <li><strong>Metode MOORA:</strong> Sangat tegas memisahkan nilai positif (Benefit)
                                            dan negatif (Cost). Cocok untuk melihat selisih keunggulan secara langsung.</li>
                                        <li><strong>Metode ARAS:</strong> Membandingkan siswa dengan "Standar Ideal" (Nilai
                                            Tertinggi). Metode ini lebih teliti melihat seberapa dekat siswa dengan
                                            kesempurnaan.</li>
                                    </ul>
                                    <p class="mt-2 mb-0"><strong>Tips:</strong> Jika hasilnya berbeda, diskusikan kembali
                                        kriteria mana yang lebih diprioritaskan sekolah saat ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle w-100" id="tableKomparasi">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th rowspan="2" class="align-middle">NIS</th>
                                    <th rowspan="2" class="align-middle">Nama Siswa</th>
                                    <th colspan="2" class="bg-primary border-primary">Metode MOORA</th>
                                    <th colspan="2" class="bg-success border-success">Metode ARAS</th>
                                    <th rowspan="2" class="align-middle text-warning">Status</th>
                                </tr>
                                <tr>
                                    <th class="bg-primary text-white">Nilai</th>
                                    <th class="bg-primary text-white">Rank</th>
                                    <th class="bg-success text-white">Nilai</th>
                                    <th class="bg-success text-white">Rank</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $data_komparasi = [];
                                $rank = 1;
                                foreach ($hasil_moora as $m) {
                                    $data_komparasi[$m['nis']] = [
                                        'nama' => $m['nama'],
                                        'moora_val' => $m['nilai'],
                                        'moora_rank' => $rank++
                                    ];
                                }

                                $rank = 1;
                                foreach ($hasil_aras as $a) {
                                    if (isset($data_komparasi[$a['nis']])) {
                                        $data_komparasi[$a['nis']]['aras_val'] = $a['nilai'];
                                        $data_komparasi[$a['nis']]['aras_rank'] = $rank++;
                                    }
                                }

                                foreach ($data_komparasi as $nis => $d):
                                    $selisih = abs($d['moora_rank'] - $d['aras_rank']);

                                    // Tentukan Status untuk Orang Awam
                                    if ($selisih == 0) {
                                        $status = '<span class="badge bg-success">Sangat Konsisten</span>';
                                        $bg_row = '';
                                    } elseif ($selisih <= 2) {
                                        $status = '<span class="badge bg-info text-dark">Cukup Konsisten</span>';
                                        $bg_row = '';
                                    } else {
                                        $status = '<span class="badge bg-warning text-dark">Berbeda Pandangan</span>';
                                        $bg_row = 'table-warning';
                                    }

                                    // Highlight Top 3 (Kandidat Kuat)
                                    if ($d['moora_rank'] <= 3 && $d['aras_rank'] <= 3) {
                                        $bg_row = 'table-success bg-opacity-10';
                                    }
                                    ?>
                                    <tr class="<?= $bg_row ?>">
                                        <td class="text-center fw-bold"><?= $nis ?></td>
                                        <td><?= $d['nama'] ?></td>
                                        <td class="text-end"><?= number_format($d['moora_val'], 4) ?></td>
                                        <td class="text-center fw-bold text-primary"><?= $d['moora_rank'] ?></td>
                                        <td class="text-end"><?= number_format($d['aras_val'], 4) ?></td>
                                        <td class="text-center fw-bold text-success"><?= $d['aras_rank'] ?></td>
                                        <td class="text-center"><?= $status ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        // Inisialisasi DataTables pada tabel ranking dan komparasi
        $('#tableMatriks, #tableRankMoora, #tableRankAras, #tableKomparasi').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' }
        });

        // Fix layout DataTables saat ganti tab (agar header tabel pas)
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>