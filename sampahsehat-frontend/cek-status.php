<?php 
include 'components/navbar.php'; 

function formatTanggalIndo($datetime) {
    if (!$datetime) return '-';
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $time = strtotime($datetime);
    $d = date('d', $time);
    $m = (int)date('m', $time);
    $y = date('Y', $time);
    $h_i = date('H:i', $time);
    return "$d {$bulan[$m]} $y, $h_i";
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="text-center mb-4 border-bottom pb-4">
                <div class="d-inline-flex p-3 bg-light rounded-circle text-primary mb-3">
                    <i class="bi bi-search fs-1"></i>
                </div>
                <h2 class="fw-bold text-dark">Cek Status Laporan</h2>
                <p class="text-muted small mb-0">Masukkan kode laporan Anda (contoh: LPS-20260001) untuk memantau perkembangannya.</p>
            </div>

            <div class="alert alert-light border rounded-3 small text-muted mb-4">
                Gunakan kode laporan dari halaman pengiriman atau daftar laporan publik. Setelah data ditemukan, Anda juga bisa menyalin ulang kode dari halaman ini.
            </div>

            <form action="cek-status.php" method="GET" class="mb-5">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-hash"></i></span>
                    <input type="text" name="kode" value="<?= isset($_GET['kode']) ? htmlspecialchars($_GET['kode']) : '' ?>" 
                           placeholder="LPS-XXXXX" required 
                           class="form-control border-start-0 ps-0" style="font-family: monospace;">
                    <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-search me-2"></i>Cari</button>
                </div>
            </form>

            <?php
            if (isset($_GET['kode']) && trim($_GET['kode']) !== '') {
                $kode = trim($_GET['kode']);
                $api_url = API_BASE_URL . '/api/laporan/' . urlencode($kode);
                
                $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
                $response = @file_get_contents($api_url, false, $context);
                
                $ketemu = false;
                $data = null;
                $errorMsg = null;

                if ($response !== false) {
                    $resData = json_decode($response, true);
                    if (isset($resData['success']) && $resData['success'] && isset($resData['data'])) {
                        $ketemu = true;
                        $data = $resData['data'];
                    } else {
                        $errorMsg = isset($resData['message']) ? $resData['message'] : "Laporan tidak ditemukan.";
                    }
                } else {
                    $errorMsg = "Gagal terhubung ke API Server.";
                }

                if ($ketemu && $data): 
                    $status = strtolower($data['status']);
                    
                    // Setup Status Theme & Description
                    $statusInfo = [
                        'baru' => [
                            'color' => 'info',
                            'icon'  => 'bi-inbox',
                            'title' => 'Menunggu Tinjauan',
                            'desc'  => 'Laporan Anda telah diterima dan sedang dalam antrean verifikasi oleh Admin.'
                        ],
                        'diproses' => [
                            'color' => 'warning',
                            'icon'  => 'bi-hourglass-split',
                            'title' => 'Sedang Ditindaklanjuti',
                            'desc'  => 'Laporan Anda valid dan saat ini sedang ditangani oleh petugas kebersihan di lapangan.'
                        ],
                        'selesai' => [
                            'color' => 'success',
                            'icon'  => 'bi-check-circle-fill',
                            'title' => 'Selesai Ditangani',
                            'desc'  => 'Tumpukan sampah telah berhasil dibersihkan. Terima kasih atas kepedulian Anda terhadap lingkungan!'
                        ],
                        'ditolak' => [
                            'color' => 'danger',
                            'icon'  => 'bi-x-circle-fill',
                            'title' => 'Laporan Ditolak',
                            'desc'  => 'Mohon maaf, laporan Anda tidak dapat diproses karena data tidak valid atau di luar jangkauan kami.'
                        ]
                    ];
                    
                    $currentStatus = $statusInfo[$status] ?? $statusInfo['baru'];
            ?>
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-primary text-white p-4 d-flex justify-content-between align-items-center gap-2 flex-wrap border-0">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-file-earmark-text me-2"></i>Detail Laporan</h5>
                            <span class="small text-white-50">Dilaporkan pada: <?= formatTanggalIndo($data['dibuat_pada']) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 px-3 py-2 rounded-3 border border-white border-opacity-25">
                            <span class="font-monospace fw-bold fs-5"><?= htmlspecialchars($data['kode_laporan']) ?></span>
                            <button
                                type="button"
                                class="btn btn-sm btn-light text-primary border-0 ms-2"
                                onclick="salinKodeStatus('<?= htmlspecialchars($data['kode_laporan'], ENT_QUOTES) ?>', this)"
                                title="Salin kode laporan"
                            >
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- BANNER STATUS -->
                        <div class="bg-<?= $currentStatus['color'] ?>-subtle p-4 border-bottom border-<?= $currentStatus['color'] ?>-subtle">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-<?= $currentStatus['color'] ?> text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; font-size: 28px;">
                                    <i class="bi <?= $currentStatus['icon'] ?>"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold text-<?= $currentStatus['color'] ?>-emphasis mb-1">
                                        <?= htmlspecialchars($data['label_status']) ?>
                                    </h4>
                                    <p class="text-<?= $currentStatus['color'] ?>-emphasis mb-0">
                                        <?= $currentStatus['desc'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 p-md-5 bg-white">
                            <div class="row g-4">
                                <!-- BOX 1: Informasi Pelapor -->
                                <div class="col-md-6">
                                    <div class="bg-light border border-secondary-subtle rounded-4 p-4 h-100 d-flex flex-column">
                                        <h6 class="fw-bold text-primary text-uppercase small mb-4"><i class="bi bi-person-badge me-2"></i>Informasi Pelapor</h6>
                                        
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Nama Pelapor</div>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($data['nama_pelapor']) ?></div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="text-muted small mb-1">Kontak</div>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($data['kontak_pelapor']) ?></div>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <div class="text-muted small mb-2">Kategori Sampah</div>
                                            <div class="d-inline-block fw-medium text-dark border border-secondary-subtle px-3 py-1 bg-white rounded-pill small">
                                                <?= htmlspecialchars($data['kategori']['nama_kategori'] ?? 'Lainnya') ?> 
                                                <?php if(isset($data['kategori']['label_risiko'])): ?>
                                                    <span class="badge bg-secondary ms-1"><?= htmlspecialchars($data['kategori']['label_risiko']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- BOX 2: Detail Kejadian -->
                                <div class="col-md-6">
                                    <div class="bg-light border border-secondary-subtle rounded-4 p-4 h-100 d-flex flex-column">
                                        <h6 class="fw-bold text-primary text-uppercase small mb-4"><i class="bi bi-geo-alt me-2"></i>Detail Kejadian</h6>
                                        
                                        <div class="mb-3">
                                            <div class="text-muted small mb-2">Lokasi Kejadian</div>
                                            <div class="d-flex align-items-start gap-2 text-dark bg-white p-3 rounded-3 border border-secondary-subtle">
                                                <i class="bi bi-geo-alt-fill text-danger mt-1"></i>
                                                <span class="small fw-medium"><?= htmlspecialchars($data['lokasi']) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <div class="text-muted small mb-2">Deskripsi</div>
                                            <div class="text-dark small border border-secondary-subtle p-3 rounded-3 bg-white">
                                                <?= nl2br(htmlspecialchars($data['deskripsi'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- BOX 3: Tindak Lanjut Petugas -->
                                <div class="col-md-6">
                                    <div class="bg-light border border-secondary-subtle rounded-4 p-4 h-100 d-flex flex-column">
                                        <h6 class="fw-bold text-primary text-uppercase small mb-4"><i class="bi bi-tools me-2"></i>Tindak Lanjut Petugas</h6>
                                        
                                        <?php if (!empty($data['petugas'])): ?>
                                            <div class="d-flex align-items-center gap-3 bg-white border border-secondary-subtle p-3 rounded-3 my-auto">
                                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0" style="width: 50px; height: 50px; font-size: 20px;">
                                                    <?= strtoupper(substr($data['petugas']['nama'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($data['petugas']['nama']) ?></div>
                                                    <?php if (!empty($data['petugas']['spesialisasi'])): ?>
                                                        <div class="small text-muted mb-1"><i class="bi bi-cone-striped me-1"></i><?= htmlspecialchars($data['petugas']['spesialisasi']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($data['petugas']['kontak'])): ?>
                                                        <div class="small text-muted"><i class="bi bi-telephone-fill text-success me-1"></i><?= htmlspecialchars($data['petugas']['kontak']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center my-auto py-3 text-muted">
                                                <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary opacity-25"></i>
                                                <span class="small">Belum ada petugas yang ditugaskan.</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- BOX 4: Catatan Admin / Petugas -->
                                <div class="col-md-6">
                                    <div class="bg-primary bg-opacity-10 border border-primary-subtle rounded-4 p-4 h-100 d-flex flex-column position-relative">
                                        <i class="bi bi-quote fs-1 text-primary opacity-25 position-absolute top-0 end-0 me-4 mt-3"></i>
                                        <h6 class="fw-bold text-primary text-uppercase small mb-4 position-relative z-1"><i class="bi bi-chat-quote me-2"></i>Catatan Admin / Petugas</h6>
                                        
                                        <div class="my-auto position-relative z-1">
                                            <?php if (!empty($data['catatan_petugas'])): ?>
                                                <div class="small fw-medium text-dark bg-white bg-opacity-75 p-3 rounded-3 border border-primary-subtle shadow-sm">
                                                    <?= nl2br(htmlspecialchars($data['catatan_petugas'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center text-muted py-3">
                                                    <span class="fst-italic small">Belum ada catatan penyelesaian.</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if(!empty($data['foto_url'])): ?>
                            <div class="mt-4 pt-2">
                                <h6 class="fw-bold text-secondary text-uppercase small mb-3 px-1"><i class="bi bi-camera me-2"></i>Foto Bukti Laporan</h6>
                                <div class="text-center bg-light border border-secondary-subtle rounded-4 p-2 overflow-hidden shadow-sm">
                                    <img src="<?= htmlspecialchars($data['foto_url']) ?>" alt="Foto Bukti Laporan" class="img-fluid rounded-3" style="max-height: 400px; object-fit: contain;">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-danger d-flex align-items-center rounded-3 p-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Pencarian Gagal</h6>
                        <span class="small"><?= htmlspecialchars($errorMsg) ?></span>
                    </div>
                </div>
            <?php endif; 
            } ?>
        </div>
    </div>
</div>

<script>
function salinKodeStatus(kode, tombol) {
    const isiAwal = tombol.innerHTML;

    function tampilBerhasil() {
        tombol.innerHTML = '<i class="bi bi-clipboard-check me-1"></i>Tersalin!';
        tombol.classList.remove('btn-light', 'text-primary');
        tombol.classList.add('btn-success', 'text-white');

        setTimeout(() => {
            tombol.innerHTML = isiAwal;
            tombol.classList.add('btn-light', 'text-primary');
            tombol.classList.remove('btn-success', 'text-white');
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(kode).then(tampilBerhasil).catch(fallbackSalin);
        return;
    }

    fallbackSalin();

    function fallbackSalin() {
        const area = document.createElement('textarea');
        area.value = kode;
        area.setAttribute('readonly', '');
        area.style.position = 'absolute';
        area.style.left = '-9999px';
        document.body.appendChild(area);
        area.select();
        document.execCommand('copy');
        document.body.removeChild(area);
        tampilBerhasil();
    }
}
</script>
<?php include 'components/footer.php'; ?>
