<?php include 'components/navbar.php'; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-list-ul text-primary me-2"></i>Daftar Laporan Publik</h2>
            <p class="text-muted small">Daftar laporan pengaduan sampah yang masuk dari masyarakat. Identitas pelapor disembunyikan demi privasi.</p>
        </div>

        <?php
        $data = [];
        $api_url = API_BASE_URL . '/laporan-publik';
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
        $response = @file_get_contents($api_url, false, $context);
        if ($response !== false) {
            $resData = json_decode($response, true);
            if (isset($resData['success']) && $resData['success'] && isset($resData['data'])) {
                $data = $resData['data'];
            }
        }
        ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Kode Laporan</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Kategori</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Petugas</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Status</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada laporan publik yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data as $row): ?>
                            <tr>
                                <td class="fw-semibold text-dark font-monospace" style="font-size: 0.9rem;">
                                    <?= htmlspecialchars($row['kode_laporan']) ?>
                                    <button class="btn btn-sm btn-light border-0 py-0 px-1 ms-1 copy-kode" data-kode="<?= htmlspecialchars($row['kode_laporan']) ?>" title="Salin kode laporan" style="font-size:0.75rem;">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td class="small">
                                    <?php if (!empty($row['petugas'])): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($row['petugas']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">–</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $status = strtolower($row['status']);
                                        $badgeClass = 'bg-secondary';
                                        if ($status == 'baru') $badgeClass = 'bg-info text-dark';
                                        elseif ($status == 'diproses') $badgeClass = 'bg-warning text-dark';
                                        elseif ($status == 'selesai') $badgeClass = 'bg-success';
                                        elseif ($status == 'ditolak') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge rounded-pill <?= $badgeClass ?> px-3 py-2 fw-medium">
                                        <?= htmlspecialchars($row['label_status'] ?? ucfirst($status)) ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php
                                        $tgl = isset($row['dibuat_pada']) ? strtotime($row['dibuat_pada']) : time();
                                        echo date('d M Y, H:i', $tgl);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.copy-kode').forEach(btn => {
    btn.addEventListener('click', function() {
        const kode = this.dataset.kode;
        navigator.clipboard.writeText(kode).then(() => {
            const icon = this.querySelector('i');
            const orig = icon.className;
            icon.className = 'bi bi-check-lg text-success';
            setTimeout(() => { icon.className = orig; }, 1500);
        }).catch(() => {
            alert('Gagal menyalin. Silakan salin manual: ' + kode);
        });
    });
});
</script>
<?php include 'components/footer.php'; ?>