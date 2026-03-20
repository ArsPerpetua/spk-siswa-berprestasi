<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-diagram-3-fill me-2"></i> Pembobotan Kriteria (Metode AHP)</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill me-2"></i>
            Silakan bandingkan tingkat kepentingan antar kriteria di bawah ini.
            <br><strong>Skala 1:</strong> Sama penting, <strong>Skala 9:</strong> Mutlak lebih penting [Saaty].
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- FITUR PRESET -->
        <div class="card bg-light border-primary mb-4">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-primary mb-1"><i class="bi bi-lightning-charge-fill"></i> Quick Preset</h6>
                    <small class="text-muted" id="presetDesc">Pilih skenario untuk mengisi form secara otomatis.</small>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select" id="selectPreset">
                        <option value="" selected disabled>-- Pilih Skenario --</option>
                        <?php foreach ($presets as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary" onclick="applyPreset()"><i class="bi bi-check-lg"></i>
                        Terapkan</button>

                    <!-- Tombol Hapus (Muncul jika preset user dipilih) -->
                    <a href="#" id="btnDeletePreset" class="btn btn-outline-danger d-none"
                        onclick="return confirm('Hapus preset ini?')"><i class="bi bi-trash"></i></a>

                    <!-- Tombol Simpan -->
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#modalSimpanPreset"><i class="bi bi-save"></i> Simpan Baru</button>
                </div>
            </div>
        </div>

        <form action="<?= base_url('ahp/proses') ?>" method="post">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="20%">Kriteria A</th>
                            <th width="40%">Perbandingan (Skala 1-9)</th>
                            <th width="20%">Kriteria B</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n = count($kriteria);
                        // Looping Segitiga Atas untuk membuat pasangan
                        for ($i = 0; $i < $n; $i++):
                            for ($j = $i + 1; $j < $n; $j++):
                                $k1 = $kriteria[$i];
                                $k2 = $kriteria[$j];
                                ?>
                                <tr>
                                    <td class="fw-bold text-end"><?= kriteria_label($k1['nama_kriteria']) ?> (<?= $k1['kode_kriteria'] ?>)</td>

                                    <td class="text-center bg-white border">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-6">
                                                <select name="nilai_<?= $k1['id_kriteria'] ?>_<?= $k2['id_kriteria'] ?>"
                                                    class="form-select form-select-sm" required>
                                                    <option value="1">1 - Sama Penting</option>
                                                    <option value="2">2 - Mendekati Sedikit Lebih</option>
                                                    <option value="3">3 - Sedikit Lebih Penting</option>
                                                    <option value="4">4 - Mendekati Lebih Penting</option>
                                                    <option value="5">5 - Lebih Penting</option>
                                                    <option value="6">6 - Mendekati Sangat Penting</option>
                                                    <option value="7">7 - Sangat Penting</option>
                                                    <option value="8">8 - Mendekati Mutlak</option>
                                                    <option value="9">9 - Mutlak Lebih Penting</option>
                                                </select>
                                            </div>

                                            <div class="col-6">
                                                <select name="pilih_<?= $k1['id_kriteria'] ?>_<?= $k2['id_kriteria'] ?>"
                                                    class="form-select form-select-sm bg-light" required>
                                                    <option value="<?= $k1['id_kriteria'] ?>">Lebih Penting Kiri
                                                        (<?= $k1['kode_kriteria'] ?>)</option>
                                                    <option value="<?= $k2['id_kriteria'] ?>">Lebih Penting Kanan
                                                        (<?= $k2['kode_kriteria'] ?>)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="fw-bold text-start"><?= kriteria_label($k2['nama_kriteria']) ?> (<?= $k2['kode_kriteria'] ?>)
                                    </td>
                                </tr>
                            <?php
                            endfor;
                        endfor;
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="d-grid gap-2 col-md-4 mx-auto mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-calculator"></i> Hitung Bobot AHP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Simpan Preset -->
<div class="modal fade" id="modalSimpanPreset" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= base_url('ahp/simpanPreset') ?>" method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Simpan Konfigurasi Saat Ini</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Preset</label>
                    <input type="text" name="nama_preset" class="form-control"
                        placeholder="Contoh: Fokus Ekstrakurikuler" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi Singkat</label>
                    <input type="text" name="deskripsi" class="form-control" placeholder="Keterangan tambahan...">
                </div>
                <input type="hidden" name="data_json" id="inputDataJson">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" onclick="prepareSave()">Simpan ke Database</button>
            </div>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    // 1. Siapkan Data dari PHP ke JS
    const presets = <?= json_encode($presets) ?>;

    // Mapping ID Kriteria berdasarkan Index urutan (0, 1, 2...)
    // Agar preset '0|1' bisa diterjemahkan menjadi ID database yang acak
    const kriteriaIds = [
        <?php foreach ($kriteria as $k)
            echo $k['id_kriteria'] . ','; ?>
    ];

    // 2. Event Listener saat Dropdown Berubah
    document.getElementById('selectPreset').addEventListener('change', function () {
        const selectedId = this.value;
        const preset = presets.find(p => p.id === selectedId);
        if (preset) {
            document.getElementById('presetDesc').innerText = preset.deskripsi;

            // Tampilkan tombol hapus jika ID-nya angka (berarti dari database)
            const btnDel = document.getElementById('btnDeletePreset');
            if (!isNaN(selectedId)) {
                btnDel.classList.remove('d-none');
                btnDel.href = "<?= base_url('ahp/hapusPreset/') ?>/" + selectedId;
            } else {
                btnDel.classList.add('d-none');
            }
        }
    });

    // 3. Fungsi Terapkan Preset
    function applyPreset() {
        const selectedId = document.getElementById('selectPreset').value;
        if (!selectedId) return alert('Pilih preset terlebih dahulu!');

        const preset = presets.find(p => p.id == selectedId);
        if (!preset) return;

        // Reset semua ke default (1 - Sama Penting)
        document.querySelectorAll('select[name^="nilai_"]').forEach(el => el.value = "1");

        // Reset pilihan dominan ke default (Kiri / ID pertama)
        document.querySelectorAll('select[name^="pilih_"]').forEach(el => {
            // Ambil ID pertama dari opsi select (biasanya opsi ke-0 atau ke-1)
            el.value = el.options[0].value;
        });

        // LOGIKA 1: Preset Bawaan (Logic Index)
        if (preset.type === 'logic') {
            for (const [key, val] of Object.entries(preset.data)) {
                const [idx1, idx2] = key.split('|').map(Number);
                const id1 = kriteriaIds[idx1];
                const id2 = kriteriaIds[idx2];

                const nilai = val[0];
                const winnerIndex = val[1];
                const winnerId = (winnerIndex === 0) ? id1 : id2;

                const inputNilai = document.querySelector(`select[name="nilai_${id1}_${id2}"]`);
                if (inputNilai) inputNilai.value = nilai;

                const inputPilih = document.querySelector(`select[name="pilih_${id1}_${id2}"]`);
                if (inputPilih) inputPilih.value = winnerId;
            }
        }
        // LOGIKA 2: Preset User (Raw Form Data)
        else if (preset.type === 'raw') {
            for (const [key, val] of Object.entries(preset.data)) {
                const el = document.getElementsByName(key)[0];
                if (el) el.value = val;
            }
        }

        alert('Preset "' + preset.nama + '" berhasil diterapkan!');
    }

    // 4. Fungsi Persiapan Simpan (Ambil data form saat ini)
    function prepareSave() {
        const formData = {};
        // Ambil semua select nilai dan pilih
        document.querySelectorAll('select[name^="nilai_"], select[name^="pilih_"]').forEach(el => {
            formData[el.name] = el.value;
        });
        // Masukkan ke input hidden sebagai JSON string
        document.getElementById('inputDataJson').value = JSON.stringify(formData);
    }
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
