<?php
include 'components/navbar.php';

// Ambil daftar kategori dari API (dengan fallback dummy)
$kategoriList = [];
$api_url  = API_BASE_URL . '/api/kategori';
$context  = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
$response = @file_get_contents($api_url, false, $context);
if ($response !== false) {
    $resData = json_decode($response, true);
    if (isset($resData['success']) && $resData['success'] && isset($resData['data'])) {
        $kategoriList = $resData['data'];
    }
}

// Fallback kategori dummy jika API tidak tersedia
if (empty($kategoriList)) {
    $kategoriList = [
        ['id' => 1, 'nama_kategori' => 'Sampah Organik',          'label_risiko' => 'Rendah'],
        ['id' => 2, 'nama_kategori' => 'Sampah Kertas & Kardus',  'label_risiko' => 'Rendah'],
        ['id' => 3, 'nama_kategori' => 'Sampah Kaca & Keramik',   'label_risiko' => 'Rendah'],
        ['id' => 4, 'nama_kategori' => 'Sampah Plastik',          'label_risiko' => 'Sedang'],
        ['id' => 5, 'nama_kategori' => 'Sampah Logam & Kaleng',   'label_risiko' => 'Sedang'],
        ['id' => 6, 'nama_kategori' => 'Sampah Tekstil & Pakaian','label_risiko' => 'Sedang'],
        ['id' => 7, 'nama_kategori' => 'Sampah Elektronik (E-Waste)','label_risiko'=> 'Sedang'],
        ['id' => 8, 'nama_kategori' => 'Sampah Medis & Bahan Infeksius','label_risiko'=>'Tinggi'],
        ['id' => 9, 'nama_kategori' => 'Limbah B3',               'label_risiko' => 'Tinggi'],
        ['id' => 10,'nama_kategori' => 'Sampah Konstruksi & Bangunan','label_risiko'=>'Tinggi'],
    ];
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="mb-4 border-bottom pb-3">
                <h2 class="fw-bold text-dark mb-1">
                    <i class="bi bi-megaphone-fill text-success me-2"></i>Kirim Laporan Sampah
                </h2>
                <p class="text-muted small mb-0">Gunakan form di bawah ini dan tandai lokasi via peta interaktif.</p>
            </div>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-danger text-center p-4 rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-danger d-block mb-3"></i>
                    <h5 class="fw-bold text-danger">Akses Ditolak</h5>
                    <p class="small mb-4">Anda harus masuk menggunakan akun Pelapor terlebih dahulu untuk membuat pengaduan.</p>
                    <a href="login.php" class="btn btn-primary-custom px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Log In Sekarang
                    </a>
                </div>

            <?php elseif (($_SESSION['role'] ?? '') !== 'Pelapor'): ?>
                <div class="alert alert-warning text-center p-4 rounded-3" role="alert">
                    <i class="bi bi-person-lock fs-1 text-warning d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">Form Hanya Untuk Pelapor</h5>
                    <p class="small mb-4">
                        Akun <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>
                        tidak dapat membuat laporan dari halaman ini.
                    </p>
                    <a href="index.php" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>

            <?php else: ?>
                <!-- Alert pesan hasil submit -->
                <div id="alertMessage" class="alert d-none" role="alert"></div>

                <form id="formLaporan" enctype="multipart/form-data">
                    <div class="row g-3">

                        <!-- Nama Pelapor -->
                        <div class="col-md-6">
                            <label for="nama_pelapor" class="form-label fw-semibold text-secondary small">
                                Nama Pelapor <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nama_pelapor" name="nama_pelapor"
                                   class="form-control"
                                   placeholder="Masukkan nama Anda" required>
                        </div>

                        <!-- Nomor Telepon -->
                        <div class="col-md-6">
                            <label for="kontak_pelapor" class="form-label fw-semibold text-secondary small">
                                Nomor Telepon <span class="text-danger">*</span>
                            </label>
                            <input type="tel" id="kontak_pelapor" name="kontak_pelapor"
                                   class="form-control"
                                   placeholder="Contoh: 08123456789" required>
                        </div>

                        <!-- Kategori Sampah -->
                        <div class="col-12">
                            <label for="kategori_id" class="form-label fw-semibold text-secondary small">
                                Kategori Sampah <span class="text-danger">*</span>
                            </label>
                            <select id="kategori_id" name="kategori_id" required class="form-select">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                    <option value="<?= htmlspecialchars($kat['id']) ?>">
                                        <?= htmlspecialchars($kat['label_risiko']) ?> - <?= htmlspecialchars($kat['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Peta -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">
                                Pilih Lokasi di Peta (Wajib) <span class="text-danger">*</span>
                            </label>
                            <div id="map" class="rounded-3 border border-secondary-subtle"
                                 style="height: 300px; z-index: 1;"></div>
                            <input type="hidden" id="latitude">
                            <input type="hidden" id="longitude">
                            <div class="form-text text-danger d-none" id="mapError">
                                Silakan klik pada peta untuk menentukan lokasi.
                            </div>
                        </div>

                        <!-- Detail Lokasi -->
                        <div class="col-12">
                            <label for="lokasi" class="form-label fw-semibold text-secondary small">
                                Detail Lokasi Patokan <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="lokasi" name="lokasi"
                                   class="form-control"
                                   placeholder="Misal: Dekat gapura masuk perumahan" required>
                        </div>

                        <!-- Deskripsi -->
                        <div class="col-12">
                            <label for="deskripsi" class="form-label fw-semibold text-secondary small">
                                Deskripsi Laporan <span class="text-danger">*</span>
                            </label>
                            <textarea id="deskripsi" name="deskripsi" rows="4"
                                      class="form-control"
                                      placeholder="Ceritakan kondisi tumpukan sampah secara detail..."
                                      required></textarea>
                        </div>

                        <!-- Foto -->
                        <div class="col-12">
                            <label for="foto" class="form-label fw-semibold text-secondary small">
                                Foto Bukti <span class="text-muted">(Opsional)</span>
                            </label>
                            <input type="file" id="foto" name="foto"
                                   class="form-control"
                                   accept="image/jpeg,image/png,image/jpg,image/webp">
                            <div class="form-text">Maksimal 2MB. Format: JPG, JPEG, PNG, WEBP.</div>
                        </div>

                        <!-- Info -->
                        <div class="col-12">
                            <div class="bg-light border rounded-3 px-3 py-2 small text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Setelah laporan berhasil dikirim, kode laporan akan ditampilkan dan bisa langsung disalin.
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="col-12 mt-2">
                            <button type="submit" id="btnSubmit"
                                    class="btn btn-primary-custom w-100 py-2 fs-6 shadow-sm">
                                <i class="bi bi-send me-2"></i>Kirim Laporan Resmi
                            </button>
                        </div>

                    </div><!-- /row -->
                </form>

                <script>
                // ─── Inisialisasi Peta Leaflet ───────────────────────────────
                (function () {
                    var lapMap = L.map('map').setView([-7.2575, 112.7521], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(lapMap);

                    var lapMarker = null;

                    function updateCoords(latlng) {
                        document.getElementById('latitude').value  = latlng.lat.toFixed(6);
                        document.getElementById('longitude').value = latlng.lng.toFixed(6);
                        document.getElementById('mapError').classList.add('d-none');
                    }

                    lapMap.on('click', function (e) {
                        if (lapMarker) {
                            lapMarker.setLatLng(e.latlng);
                        } else {
                            lapMarker = L.marker(e.latlng, { draggable: true }).addTo(lapMap);
                            lapMarker.on('dragend', function () { updateCoords(lapMarker.getLatLng()); });
                        }
                        updateCoords(e.latlng);
                    });

                    // ─── Helpers ─────────────────────────────────────────────
                    function escapeHtml(value) {
                        return String(value ?? '').replace(/[&<>"']/g, function (c) {
                            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
                        });
                    }

                    function salinTeks(kode, tombol) {
                        var awal = tombol.innerHTML;
                        function ok() {
                            tombol.innerHTML = '<i class="bi bi-clipboard-check me-2"></i>Tersalin!';
                            tombol.classList.replace('btn-outline-primary', 'btn-success');
                            setTimeout(function () {
                                tombol.innerHTML = awal;
                                tombol.classList.replace('btn-success', 'btn-outline-primary');
                            }, 2000);
                        }
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(kode).then(ok).catch(fallback);
                        } else { fallback(); }
                        function fallback() {
                            var ta = document.createElement('textarea');
                            ta.value = kode;
                            ta.style.cssText = 'position:absolute;left:-9999px';
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            document.body.removeChild(ta);
                            ok();
                        }
                    }

                    function tampilkanKartuSukses(kode) {
                        var alertBox = document.getElementById('alertMessage');
                        var kodeAman = escapeHtml(kode);
                        alertBox.className = 'alert alert-success mt-3';
                        alertBox.innerHTML =
                            '<div class="card border-0 shadow-sm">' +
                            '  <div class="card-body text-center p-4">' +
                            '    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3" style="width:64px;height:64px;">' +
                            '      <i class="bi bi-check-circle-fill text-success fs-2"></i>' +
                            '    </div>' +
                            '    <h5 class="fw-bold text-dark mb-2">Laporan Berhasil Dikirim</h5>' +
                            '    <p class="text-muted mb-3">Simpan kode laporan berikut untuk mengecek status penanganan.</p>' +
                            '    <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 border rounded-3 bg-light p-3 mb-3">' +
                            '      <span class="font-monospace fw-bold fs-5 text-primary user-select-all">' + kodeAman + '</span>' +
                            '      <button type="button" id="btnSalinKode" class="btn btn-outline-primary btn-sm">' +
                            '        <i class="bi bi-clipboard me-2"></i>Salin Kode' +
                            '      </button>' +
                            '    </div>' +
                            '    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">' +
                            '      <a href="cek-status.php?kode=' + encodeURIComponent(kode) + '" class="btn btn-success"><i class="bi bi-search me-2"></i>Cek Status</a>' +
                            '      <a href="daftar-laporan.php" class="btn btn-outline-secondary"><i class="bi bi-list-ul me-2"></i>Daftar Laporan</a>' +
                            '    </div>' +
                            '  </div>' +
                            '</div>';
                        alertBox.classList.remove('d-none');

                        var btnSalin = document.getElementById('btnSalinKode');
                        if (btnSalin) {
                            btnSalin.addEventListener('click', function () { salinTeks(kode, this); });
                        }
                    }

                    // ─── Submit Form ─────────────────────────────────────────
                    document.getElementById('formLaporan').addEventListener('submit', async function (e) {
                        e.preventDefault();

                        var lat      = document.getElementById('latitude').value;
                        var lng      = document.getElementById('longitude').value;
                        var kategori = document.getElementById('kategori_id').value;
                        var deskripsi= document.getElementById('deskripsi').value.trim();
                        var fileInput= document.getElementById('foto');
                        var alertBox = document.getElementById('alertMessage');
                        var btnSubmit= document.getElementById('btnSubmit');

                        // Validasi peta
                        if (!lat || !lng) {
                            document.getElementById('mapError').classList.remove('d-none');
                            document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
                            return;
                        }

                        // Validasi kategori
                        if (!kategori) {
                            alertBox.className = 'alert alert-danger mt-3';
                            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Silakan pilih kategori sampah terlebih dahulu.';
                            alertBox.classList.remove('d-none');
                            return;
                        }

                        // Validasi ukuran foto
                        if (fileInput.files.length > 0 && fileInput.files[0].size > 2 * 1024 * 1024) {
                            alertBox.className = 'alert alert-danger mt-3';
                            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Ukuran foto melebihi 2MB. Pilih file yang lebih kecil.';
                            alertBox.classList.remove('d-none');
                            return;
                        }

                        // Loading state
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Mengirim...';
                        alertBox.className  = 'alert d-none';

                        // Siapkan FormData
                        var formData = new FormData();
                        formData.append('nama_pelapor',   document.getElementById('nama_pelapor').value.trim());
                        formData.append('kontak_pelapor', document.getElementById('kontak_pelapor').value.trim());
                        formData.append('kategori_id',    kategori);
                        formData.append('lokasi',         document.getElementById('lokasi').value.trim());
                        formData.append('latitude',       lat);
                        formData.append('longitude',      lng);
                        formData.append('deskripsi',      deskripsi);
                        if (fileInput.files.length > 0) {
                            formData.append('foto', fileInput.files[0]);
                        }

                        try {
                            var apiUrl = '<?= API_BASE_URL ?>/api/laporan';
                            var res    = await fetch(apiUrl, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json' },
                                body: formData
                            });
                            var result = await res.json().catch(function () { return {}; });

                            if (res.ok && result.success) {
                                var kodeLaporan = (result.data && result.data.kode_laporan) ? result.data.kode_laporan : '';
                                this.reset();
                                if (lapMarker) { lapMap.removeLayer(lapMarker); lapMarker = null; }
                                document.getElementById('latitude').value  = '';
                                document.getElementById('longitude').value = '';
                                document.getElementById('mapError').classList.add('d-none');

                                if (kodeLaporan) {
                                    tampilkanKartuSukses(kodeLaporan);
                                } else {
                                    alertBox.className = 'alert alert-success mt-3';
                                    alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + (result.message || 'Laporan berhasil dikirim.');
                                    alertBox.classList.remove('d-none');
                                }
                                alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });

                            } else {
                                var errMsg = result.message || 'Terjadi kesalahan saat mengirim data.';
                                if (result.errors) {
                                    errMsg += '<ul class="mb-0 mt-2">';
                                    for (var field in result.errors) {
                                        errMsg += '<li>' + result.errors[field][0] + '</li>';
                                    }
                                    errMsg += '</ul>';
                                }
                                alertBox.className = 'alert alert-danger mt-3';
                                alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + errMsg;
                                alertBox.classList.remove('d-none');
                            }

                        } catch (err) {
                            alertBox.className = 'alert alert-danger mt-3';
                            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal terhubung ke server API. Periksa koneksi internet Anda.';
                            alertBox.classList.remove('d-none');
                        } finally {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Laporan Resmi';
                        }
                    });

                })(); // end IIFE
                </script>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
