<?php include 'components/navbar.php'; 

// Fetch categories from API
$kategoriList = [];
$api_url = API_BASE_URL . '/kategori';
$context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
$response = @file_get_contents($api_url, false, $context);
if ($response !== false) {
    $resData = json_decode($response, true);
    if (isset($resData['success']) && $resData['success'] && isset($resData['data'])) {
        $kategoriList = $resData['data'];
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="mb-4 border-bottom pb-3">
                <h2 class="fw-bold text-dark mb-1"><i class="bi bi-megaphone-fill text-success me-2"></i>Kirim Laporan Sampah</h2>
                <p class="text-muted small mb-0">Gunakan form di bawah ini dan tandai lokasi via peta interaktif.</p>
            </div>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-danger text-center p-4 rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-danger d-block mb-3"></i>
                    <h5 class="fw-bold text-danger">Akses Ditolak</h5>
                    <p class="small mb-4">Anda harus masuk menggunakan akun Pelapor terlebih dahulu untuk membuat pengaduan.</p>
                    <a href="login.php" class="btn btn-primary-custom px-4">Log In Sekarang</a>
                </div>
            <?php else: ?>
                <!-- Alert untuk pesan error / success dari fetch -->
                <div id="alertMessage" class="alert d-none" role="alert"></div>

                <form id="formLaporan" enctype="multipart/form-data">
                    <!-- Session Data untuk kontak (Email) -->
                    <input type="hidden" id="kontak_pelapor" value="<?= htmlspecialchars($_SESSION['email']) ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Nama Pelapor</label>
                            <input type="text" id="nama_pelapor" value="<?= htmlspecialchars($_SESSION['nama']) ?>" readonly required 
                                   class="form-control bg-light text-muted">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary small">Kategori Sampah</label>
                            <select id="kategori_id" required class="form-select">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach($kategoriList as $kat): ?>
                                    <option value="<?= htmlspecialchars($kat['id']) ?>"><?= htmlspecialchars($kat['label_risiko']) ?> - <?= htmlspecialchars($kat['nama_kategori']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Pilih Lokasi di Peta (Wajib)</label>
                            <div id="map" class="rounded-3 border border-secondary-subtle" style="height: 300px; z-index: 1;"></div>
                            <input type="hidden" id="latitude" required>
                            <input type="hidden" id="longitude" required>
                            <div class="form-text text-danger d-none" id="mapError">Silakan klik pada peta untuk menentukan lokasi.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Detail Lokasi Patokan</label>
                            <input type="text" id="lokasi" placeholder="Misal: Dekat gapura masuk perumahan" required 
                                   class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Deskripsi Laporan</label>
                            <textarea id="deskripsi" rows="4" placeholder="Ceritakan kondisi tumpukan sampah secara detail (minimal 5 karakter)..." required 
                                      class="form-control"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Foto Bukti (Opsional)</label>
                            <input type="file" id="foto" class="form-control" accept="image/jpeg, image/png, image/jpg, image/webp">
                            <div class="form-text">Maksimal 2MB. Format: JPG, JPEG, PNG, WEBP.</div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" id="btnSubmit" class="btn btn-primary-custom w-100 py-2 fs-6 shadow-sm">
                                <i class="bi bi-send me-2"></i>Kirim Laporan Resmi
                            </button>
                        </div>
                    </div>
                </form>

                <script>
                    // Inisialisasi Map
                    const map = L.map('map').setView([-7.2575, 112.7521], 13); 
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    let marker;
                    map.on('click', function(e) {
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        document.getElementById('mapError').classList.add('d-none');

                        if (marker) {
                            marker.setLatLng(e.latlng);
                        } else {
                            marker = L.marker(e.latlng).addTo(map);
                        }
                    });

                    // AJAX Submit menggunakan Fetch API
                    document.getElementById('formLaporan').addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        const lat = document.getElementById('latitude').value;
                        const lng = document.getElementById('longitude').value;
                        if(!lat || !lng) {
                            document.getElementById('mapError').classList.remove('d-none');
                            return;
                        }

                        const btnSubmit = document.getElementById('btnSubmit');
                        const alertBox = document.getElementById('alertMessage');
                        
                        // Set loading state
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mengirim...';
                        alertBox.classList.add('d-none');
                        alertBox.classList.remove('alert-success', 'alert-danger');

                        // Siapkan FormData
                        const formData = new FormData();
                        formData.append('nama_pelapor', document.getElementById('nama_pelapor').value);
                        formData.append('kontak_pelapor', document.getElementById('kontak_pelapor').value);
                        formData.append('kategori_id', document.getElementById('kategori_id').value);
                        formData.append('lokasi', document.getElementById('lokasi').value);
                        formData.append('latitude', lat);
                        formData.append('longitude', lng);
                        formData.append('deskripsi', document.getElementById('deskripsi').value);
                        
                        const fileInput = document.getElementById('foto');
                        if(fileInput.files.length > 0) {
                            formData.append('foto', fileInput.files[0]);
                        }

                        try {
                            const apiUrl = '<?= API_BASE_URL ?>/laporan';
                            const response = await fetch(apiUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const result = await response.json();

                            if (response.ok && result.success) {
                                alertBox.className = 'alert alert-success mt-3';
                                alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + result.message;
                                this.reset();
                                if(marker) map.removeLayer(marker);
                                document.getElementById('latitude').value = '';
                                document.getElementById('longitude').value = '';
                                
                                // Scroll ke atas untuk melihat pesan sukses
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } else {
                                let errorMsg = result.message || 'Terjadi kesalahan saat mengirim data.';
                                if (result.errors) {
                                    errorMsg += '<ul class="mb-0 mt-2">';
                                    for(let field in result.errors) {
                                        errorMsg += `<li>${result.errors[field][0]}</li>`;
                                    }
                                    errorMsg += '</ul>';
                                }
                                alertBox.className = 'alert alert-danger mt-3';
                                alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + errorMsg;
                            }
                        } catch (error) {
                            alertBox.className = 'alert alert-danger mt-3';
                            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal terhubung ke server API.';
                        } finally {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="bi bi-send me-2"></i>Kirim Laporan Resmi';
                            alertBox.classList.remove('d-none');
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>