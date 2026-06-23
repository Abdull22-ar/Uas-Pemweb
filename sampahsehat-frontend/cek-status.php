<?php include 'components/navbar.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="text-center mb-4 border-bottom pb-4">
                <div class="d-inline-flex p-3 bg-light rounded-circle text-primary mb-3">
                    <i class="bi bi-search fs-1"></i>
                </div>
                <h2 class="fw-bold text-dark">Cek Status Laporan</h2>
                <p class="text-muted small mb-0">Masukkan kode laporan Anda (contoh: LPS-20260001) untuk memantau perkembangannya.</p>
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
                $api_url = API_BASE_URL . '/laporan/' . urlencode($kode);
                
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
                    $bgClass = 'bg-secondary';
                    $textClass = 'text-white';
                    
                    if ($status == 'baru') { $bgClass = 'bg-info-subtle'; $textClass = 'text-info-emphasis'; }
                    elseif ($status == 'diproses') { $bgClass = 'bg-warning-subtle'; $textClass = 'text-warning-emphasis'; }
                    elseif ($status == 'selesai') { $bgClass = 'bg-success-subtle'; $textClass = 'text-success-emphasis'; }
                    elseif ($status == 'ditolak') { $bgClass = 'bg-danger-subtle'; $textClass = 'text-danger-emphasis'; }
            ?>
                <div class="card border border-primary-subtle rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white p-3 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Detail Laporan</span>
                        <span class="font-monospace small"><?= htmlspecialchars($data['kode_laporan']) ?></span>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <span class="badge rounded-pill <?= $bgClass ?> <?= $textClass ?> px-4 py-2 fs-6 mb-2">
                                <?= htmlspecialchars($data['label_status'] ?? ucfirst($status)) ?>
                            </span>
                            <div class="text-muted small">
                                Tanggal: <?= date('d F Y, H:i', strtotime($data['dibuat_pada'])) ?>
                            </div>
                        </div>

                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold text-secondary small mb-1">Kategori Sampah</div>
                            <div class="text-dark fw-medium"><?= htmlspecialchars($data['kategori']['nama_kategori'] ?? 'Tidak diketahui') ?></div>
                        </div>

                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold text-secondary small mb-1">Lokasi Kejadian</div>
                            <div class="text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($data['lokasi']) ?></div>
                        </div>

                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold text-secondary small mb-1">Catatan dari Petugas</div>
                            <?php if (!empty($data['catatan_petugas'])): ?>
                                <div class="bg-light p-3 rounded-3 text-dark border border-secondary-subtle">
                                    <i class="bi bi-chat-left-quote text-secondary me-2"></i><?= nl2br(htmlspecialchars($data['catatan_petugas'])) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted fst-italic">Belum ada catatan dari petugas.</div>
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

<?php include 'components/footer.php'; ?>