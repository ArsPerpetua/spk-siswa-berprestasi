<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil SPK</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2,
        h3 {
            text-align: center;
            margin: 0;
        }

        h2 {
            margin-bottom: 5px;
        }

        h3 {
            margin-bottom: 20px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-left {
            text-align: left;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            margin-top: 20px;
            color: #2563eb;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>

<body>

    <h2>LAPORAN HASIL SELEKSI SISWA BERPRESTASI</h2>
    <h3>SMA NEGERI 1 UTAN</h3>
    <hr>

    <div class="section-title">RINGKASAN KRITERIA & BOBOT (AHP)</div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Jenis</th>
                <th>Bobot</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kriteria as $k): ?>
                <tr>
                    <td><?= $k['kode_kriteria'] ?></td>
                    <td class="text-left"><?= kriteria_label($k['nama_kriteria']) ?></td>
                    <td><?= ucfirst($k['jenis']) ?></td>
                    <td><?= number_format($k['bobot'], 4) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">A. HASIL PERHITUNGAN METODE MOORA</div>
    <div class="section-title" style="color:#111;font-size:12px;margin-top:0;">Pembagi per Kriteria (√Σx²)</div>
    <table>
        <thead>
            <tr>
                <?php foreach ($kriteria as $k): ?>
                    <th><?= $k['kode_kriteria'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach ($kriteria as $k): ?>
                    <td><?= number_format($moora_pembagi[$k['id_kriteria']] ?? 0, 4) ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th width="5%">Rank</th>
                <th width="15%">NIS</th>
                <th width="30%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th>Total Benefit</th>
                <th>Total Cost</th>
                <th>Nilai Akhir (Yi)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil_moora as $row): ?>
                <tr>
                    <td><?= (int) ($row['rank'] ?? 0) ?></td>
                    <td><?= $row['nis'] ?></td>
                    <td class="text-left"><?= $row['nama'] ?></td>
                    <td><?= $row['kelas'] ?></td>
                    <td><?= number_format($row['max'], 4) ?></td>
                    <td><?= number_format($row['min'], 4) ?></td>
                    <td><strong><?= number_format($row['nilai'], 4) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($contoh)): ?>
        <div class="section-title" style="color:#111;font-size:12px;">Contoh Perhitungan MOORA (<?= $contoh['nama'] ?> - <?= $contoh['nis'] ?>)</div>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Jenis</th>
                    <th>Nilai X</th>
                    <th>Pembagi</th>
                    <th>Normalisasi</th>
                    <th>Bobot</th>
                    <th>Nilai × Bobot</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contoh['moora'] as $row): ?>
                    <tr>
                        <td><?= $row['kode'] ?></td>
                        <td><?= ucfirst($row['jenis']) ?></td>
                        <td><?= number_format($row['raw'], 4) ?></td>
                        <td><?= number_format($row['pembagi'], 4) ?></td>
                        <td><?= number_format($row['norm'], 4) ?></td>
                        <td><?= number_format($row['bobot'], 4) ?></td>
                        <td><?= number_format($row['weighted'], 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            Total Benefit: <?= number_format($contoh['moora_total_benefit'], 4) ?> |
            Total Cost: <?= number_format($contoh['moora_total_cost'], 4) ?> |
            Yi: <?= number_format($contoh['moora_yi'], 4) ?>
        </p>
    <?php endif; ?>

    <div class="section-title">B. HASIL PERHITUNGAN METODE ARAS</div>
    <div class="section-title" style="color:#111;font-size:12px;margin-top:0;">Nilai Ideal A0</div>
    <table>
        <thead>
            <tr>
                <?php foreach ($kriteria as $k): ?>
                    <th><?= $k['kode_kriteria'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach ($kriteria as $k): ?>
                    <td><?= number_format($A0[$k['id_kriteria']] ?? 0, 4) ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

    <div class="section-title" style="color:#111;font-size:12px;margin-top:0;">Total Kolom (Setelah Transformasi Cost 1/x)</div>
    <table>
        <thead>
            <tr>
                <?php foreach ($kriteria as $k): ?>
                    <th><?= $k['kode_kriteria'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php foreach ($kriteria as $k): ?>
                    <td><?= number_format($aras_total_kolom[$k['id_kriteria']] ?? 0, 4) ?></td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th width="5%">Rank</th>
                <th width="15%">NIS</th>
                <th width="30%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th>Nilai Optimalitas (Si)</th>
                <th>Nilai Utilitas (Ki)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil_aras as $row): ?>
                <tr>
                    <td><?= (int) ($row['rank'] ?? 0) ?></td>
                    <td><?= $row['nis'] ?></td>
                    <td class="text-left"><?= $row['nama'] ?></td>
                    <td><?= $row['kelas'] ?></td>
                    <td><?= number_format($row['Si'], 4) ?></td>
                    <td><strong><?= number_format($row['Ki'], 4) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($contoh)): ?>
        <div class="section-title" style="color:#111;font-size:12px;">Contoh Perhitungan ARAS (<?= $contoh['nama'] ?> - <?= $contoh['nis'] ?>)</div>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Jenis</th>
                    <th>Nilai X</th>
                    <th>Transform (1/x)</th>
                    <th>Total Kolom</th>
                    <th>Normalisasi</th>
                    <th>Bobot</th>
                    <th>Nilai × Bobot</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contoh['aras'] as $row): ?>
                    <tr>
                        <td><?= $row['kode'] ?></td>
                        <td><?= ucfirst($row['jenis']) ?></td>
                        <td><?= number_format($row['raw'], 4) ?></td>
                        <td><?= number_format($row['transform'], 4) ?></td>
                        <td><?= number_format($row['total_kolom'], 4) ?></td>
                        <td><?= number_format($row['norm'], 4) ?></td>
                        <td><?= number_format($row['bobot'], 4) ?></td>
                        <td><?= number_format($row['weighted'], 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            Si: <?= number_format($contoh['aras_Si'], 4) ?> |
            Ki: <?= number_format($contoh['aras_Ki'], 4) ?>
        </p>
    <?php endif; ?>

    <div class="section-title">C. KESIMPULAN REKOMENDASI (PERINGKAT 1-3)</div>
    <p>Berdasarkan hasil perhitungan kedua metode, berikut adalah siswa dengan peringkat teratas. Nilai seri memperoleh peringkat yang sama.</p>

    <?php
    $top_moora_pdf = array_values(array_filter($hasil_moora, static fn($row) => (int) ($row['rank'] ?? 0) <= 3));
    $top_aras_pdf = array_values(array_filter($hasil_aras, static fn($row) => (int) ($row['rank'] ?? 0) <= 3));
    $jumlah_top_pdf = max(count($top_moora_pdf), count($top_aras_pdf));
    ?>

    <table style="width: 60%; margin: 0 auto;">
        <thead>
            <tr>
                <th>Rank MOORA</th>
                <th>Rekomendasi MOORA</th>
                <th>Rank ARAS</th>
                <th>Rekomendasi ARAS</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < $jumlah_top_pdf; $i++): ?>
                <tr>
                    <td><strong><?= isset($top_moora_pdf[$i]) ? (int) ($top_moora_pdf[$i]['rank'] ?? 0) : '-' ?></strong></td>
                    <td>
                        <?= isset($top_moora_pdf[$i]) ? $top_moora_pdf[$i]['nama'] : '-' ?>
                        <br><small>(Nilai:
                            <?= isset($top_moora_pdf[$i]) ? number_format($top_moora_pdf[$i]['nilai'], 4) : 0 ?>)</small>
                    </td>
                    <td><strong><?= isset($top_aras_pdf[$i]) ? (int) ($top_aras_pdf[$i]['rank'] ?? 0) : '-' ?></strong></td>
                    <td>
                        <?= isset($top_aras_pdf[$i]) ? $top_aras_pdf[$i]['nama'] : '-' ?>
                        <br><small>(Nilai:
                            <?= isset($top_aras_pdf[$i]) ? number_format($top_aras_pdf[$i]['Ki'], 4) : 0 ?>)</small>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <?php if (! empty($perbandingan_per_kelas)): ?>
        <div class="section-title">D. PERBANDINGAN SEBELUM DAN SESUDAH SPK PER KELAS</div>
        <?php if (! empty($selected_periode)): ?>
            <p>Periode ranking lama: <strong><?= esc($selected_periode['nama_periode']) ?></strong>
                (<?= esc($selected_periode['tahun_ajaran'] . ' ' . $selected_periode['semester']) ?>).</p>
        <?php endif ?>
        <table>
            <thead>
                <tr><th>Kelas</th><th>Sebelum Metode</th><th>MOORA</th><th>ARAS</th><th>Status</th><th>Penjelasan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($perbandingan_per_kelas as $row): ?>
                    <tr>
                        <td><?= esc($row['kelas']) ?></td>
                        <td><?= esc($row['lama']['nama_siswa'] ?? '-') ?></td>
                        <td><?= esc($row['moora']['nama'] ?? '-') ?></td>
                        <td><?= esc($row['aras']['nama'] ?? '-') ?></td>
                        <td><?= esc($row['status']) ?></td>
                        <td class="text-left"><?= esc($row['penjelasan']) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <div class="footer">
        <p>Utan, <?= date('d F Y') ?></p>
        <br><br><br>
        <p><strong>Kepala Sekolah / Admin</strong></p>
    </div>

</body>

</html>
