<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Koordinator') {
    echo "<script>alert('Akses Ditolak: Halaman Rekap Laporan khusus untuk Koordinator.'); window.location='index.php';</script>";
    exit();
}
include 'components/navbar.php'; 
?>

<?php
// Ambil data dari API publik
$allData = [];
$totalBaru = 0; $totalDiproses = 0; $totalSelesai = 0; $totalDitolak = 0;
$errorMsg = null;

$api_url = API_BASE_URL . '/api/laporan-publik?per_page=200';
$context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
$response = @file_get_contents($api_url, false, $context);
if ($response !== false) {
    $resData = json_decode($response, true);
    if (isset($resData['success']) && $resData['success'] && isset($resData['data'])) {
        $allData = $resData['data'];
        foreach ($allData as $item) {
            $st = strtolower($item['status'] ?? '');
            if ($st === 'baru') $totalBaru++;
            elseif ($st === 'diproses') $totalDiproses++;
            elseif ($st === 'selesai') $totalSelesai++;
            elseif ($st === 'ditolak') $totalDitolak++;
        }
    } else {
        $errorMsg = $resData['message'] ?? 'Data rekap belum bisa dimuat.';
    }
} else {
    $errorMsg = 'Gagal terhubung ke server API.';
}

$totalLaporan = count($allData);
$pct = $totalLaporan > 0 ? round($totalSelesai / $totalLaporan * 100) : 0;
$totalBelumSelesai = $totalBaru + $totalDiproses;
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Rekap Laporan Sampah</h2>
            <p class="text-muted small mb-0">Ringkasan statistik pengaduan sampah yang masuk dari masyarakat.</p>
        </div>
        <span class="badge bg-primary rounded-pill px-3 py-2"><i class="bi bi-database me-1"></i><?= $totalLaporan ?> Total</span>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-warning d-flex align-items-center rounded-3 p-3" role="alert">
            <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
            <div class="small"><?= htmlspecialchars($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                        <i class="bi bi-inbox-fill text-primary fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-muted small fw-semibold text-uppercase mb-0">Baru</div>
                        <div class="fw-bold fs-3 text-dark"><?= $totalBaru ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                        <i class="bi bi-gear-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase mb-0">Diproses</div>
                        <div class="fw-bold fs-3 text-dark"><?= $totalDiproses ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase mb-0">Selesai</div>
                        <div class="fw-bold fs-3 text-dark"><?= $totalSelesai ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                        <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase mb-0">Ditolak</div>
                        <div class="fw-bold fs-3 text-dark"><?= $totalDitolak ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-success me-2"></i>Peta Sebaran Laporan</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted"><?= count($allData) ?> titik</span>
                <select id="mapFilter" class="form-select form-select-sm" style="width: auto;">
                    <option value="all">Semua</option>
                    <option value="baru">Baru</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
        </div>
        <div class="card-body p-1">
            <div id="reportMap" style="height: 380px; width: 100%; border-radius: 0 0 12px 12px;"></div>
        </div>
    </div>

    <!-- Progress + Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark">Tingkat Penyelesaian</span>
                        <span class="badge bg-success rounded-pill px-3"><?= $pct ?>%</span>
                    </div>
                    <div class="progress rounded-pill mb-2" style="height: 14px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct ?>%"></div>
                    </div>
                    <div class="text-muted small"><?= $totalSelesai ?> dari <?= $totalLaporan ?> laporan selesai</div>
                    <hr>
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>Belum Selesai</span>
                        <span class="fw-bold text-danger"><?= $totalBelumSelesai ?></span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <?php $belumPct = $totalLaporan > 0 ? round($totalBelumSelesai / $totalLaporan * 100) : 0; ?>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $belumPct ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Distribusi Status</h6>
                    <canvas id="statusChart" height="180"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-tags-fill text-purple me-2"></i>Status Laporan</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-primary bg-opacity-10 rounded-3">
                            <span class="small fw-medium"><i class="bi bi-inbox text-primary me-2"></i>Baru</span>
                            <span class="fw-bold text-primary"><?= $totalBaru ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-warning bg-opacity-10 rounded-3">
                            <span class="small fw-medium"><i class="bi bi-gear text-warning me-2"></i>Diproses</span>
                            <span class="fw-bold text-warning"><?= $totalDiproses ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-success bg-opacity-10 rounded-3">
                            <span class="small fw-medium"><i class="bi bi-check-circle text-success me-2"></i>Selesai</span>
                            <span class="fw-bold text-success"><?= $totalSelesai ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-2 bg-danger bg-opacity-10 rounded-3">
                            <span class="small fw-medium"><i class="bi bi-x-circle text-danger me-2"></i>Ditolak</span>
                            <span class="fw-bold text-danger"><?= $totalDitolak ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center rounded-top-4">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul text-primary me-2"></i>Riwayat Laporan Publik</h6>
            <span class="badge bg-secondary"><?= count($allData) ?> laporan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 small text-uppercase">Kode Laporan</th>
                                <th class="py-3 small text-uppercase">Kategori</th>
                                <th class="py-3 small text-uppercase">Petugas</th>
                                <th class="py-3 small text-uppercase">Status</th>
                                <th class="py-3 small text-uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php if (empty($allData)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Belum ada laporan yang tersedia.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($allData as $row): ?>
                                <?php
                                    $status = strtolower($row['status'] ?? '');
                                    $badgeClass = 'bg-secondary';
                                    if ($status === 'baru')     $badgeClass = 'bg-info text-dark';
                                    elseif ($status === 'diproses') $badgeClass = 'bg-warning text-dark';
                                    elseif ($status === 'selesai')  $badgeClass = 'bg-success';
                                    elseif ($status === 'ditolak')  $badgeClass = 'bg-danger';
                                    $tgl = isset($row['dibuat_pada']) ? strtotime($row['dibuat_pada']) : time();
                                ?>
                                <tr>
                                    <td class="ps-4 fw-semibold font-monospace text-primary small">
                                        <?= htmlspecialchars($row['kode_laporan'] ?? '-') ?>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
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
                                        <span class="badge rounded-pill <?= $badgeClass ?> px-3 py-2">
                                            <?= htmlspecialchars($row['label_status'] ?? ucfirst($status)) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d M Y, H:i', $tgl) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.min-w-0 { min-width: 0; }
#reportMap { z-index: 1; }
.leaflet-container { border-radius: 0 0 12px 12px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Map
    var map = L.map('reportMap').setView([-7.2575, 112.7521], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    var markers = [];
    var data = <?= json_encode($allData) ?>;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(char) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[char];
        });
    }

    function getStatusColor(st) {
        switch((st || '').toLowerCase()) {
            case 'baru': return '#0d6efd';
            case 'diproses': return '#ffc107';
            case 'selesai': return '#198754';
            case 'ditolak': return '#dc3545';
            default: return '#6c757d';
        }
    }

    function createIcon(color) {
        return L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
            iconSize: [16, 16],
            iconAnchor: [8, 8],
        });
    }

    data.forEach(function(item) {
        if (item.koordinat && item.koordinat.latitude && item.koordinat.longitude) {
            var lat = parseFloat(item.koordinat.latitude);
            var lng = parseFloat(item.koordinat.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            var status = (item.status || '').toLowerCase();
            var color = getStatusColor(status);
            var marker = L.marker([lat, lng], { icon: createIcon(color) })
                .addTo(map)
                                .bindPopup(
                                    '<div style="min-width:180px;">' +
                                    '<h6 class="fw-bold mb-1" style="font-size:13px;">' + escapeHtml(item.kode_laporan || '-') + '</h6>' +
                                    '<div class="mb-1 small"><i class="bi bi-geo-alt"></i> ' + escapeHtml(item.lokasi || '-') + '</div>' +
                                    '<div class="mb-1 small"><i class="bi bi-tag"></i> ' + escapeHtml(item.kategori || '-') + '</div>' +
                                    (item.petugas ? '<div class="mb-1 small"><i class="bi bi-person-badge"></i> ' + escapeHtml(item.petugas) + '</div>' : '') +
                                    '<div class="mt-2"><span class="badge rounded-pill" style="background:' + color + ';">' + escapeHtml(item.label_status || status || '-') + '</span></div>' +
                                    '</div>'
                );
            marker._status = status;
            markers.push(marker);
        }
    });

    if (markers.length > 0) {
        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }

    document.getElementById('mapFilter').addEventListener('change', function() {
        var val = this.value;
        markers.forEach(function(m) {
            if (val === 'all' || m._status === val) {
                if (!map.hasLayer(m)) map.addLayer(m);
            } else {
                map.removeLayer(m);
            }
        });
    });

    // Status Chart
    var ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Baru', 'Diproses', 'Selesai', 'Ditolak'],
            datasets: [{
                data: [<?= $totalBaru ?>, <?= $totalDiproses ?>, <?= $totalSelesai ?>, <?= $totalDitolak ?>],
                backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } },
            cutout: '60%',
        }
    });
});
</script>

<?php include 'components/footer.php'; ?>
