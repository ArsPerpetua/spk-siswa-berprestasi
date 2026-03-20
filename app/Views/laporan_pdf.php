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
            <?php $rank = 1;
            foreach ($hasil_moora as $row): ?>
                <tr>
                    <td><?= $rank++ ?></td>
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
            <?php $rank = 1;
            foreach ($hasil_aras as $row): ?>
                <tr>
                    <td><?= $rank++ ?></td>
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

    <div class="section-title">C. KESIMPULAN REKOMENDASI (TOP 3)</div>
    <p>Berdasarkan hasil perhitungan kedua metode, berikut adalah siswa dengan peringkat teratas:</p>

    <table style="width: 60%; margin: 0 auto;">
        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Rekomendasi MOORA</th>
                <th>Rekomendasi ARAS</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < 3; $i++): ?>
                <tr>
                    <td><strong>Juara <?= $i + 1 ?></strong></td>
                    <td>
                        <?= isset($hasil_moora[$i]) ? $hasil_moora[$i]['nama'] : '-' ?>
                        <br><small>(Nilai:
                            <?= isset($hasil_moora[$i]) ? number_format($hasil_moora[$i]['nilai'], 4) : 0 ?>)</small>
                    </td>
                    <td>
                        <?= isset($hasil_aras[$i]) ? $hasil_aras[$i]['nama'] : '-' ?>
                        <br><small>(Nilai:
                            <?= isset($hasil_aras[$i]) ? number_format($hasil_aras[$i]['Ki'], 4) : 0 ?>)</small>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Utan, <?= date('d F Y') ?></p>
        <br><br><br>
        <p><strong>Kepala Sekolah / Admin</strong></p>
    </div>

</body>

</html>
