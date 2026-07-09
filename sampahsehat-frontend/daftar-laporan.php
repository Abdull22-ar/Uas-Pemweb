<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

function formatTanggalIndo($datetime) {
    if (!$datetime) return '-';
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $time = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $d = date('d', $time);
    $m = (int)date('m', $time);
    $y = date('Y', $time);
    $h_i = date('H:i', $time);
    return "$d {$bulan[$m]} $y, $h_i";
}

$currentPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ($_SERVER['PHP_SELF'] ?? ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silaris - Sistem Pengelolaan Laporan Sampah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        :root {
            --primary-color: #2e8b57;
            --secondary-color: #4CAF50;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .navbar-brand img {
            height: 36px;
        }
        .nav-link {
            font-weight: 500;
            color: #495057 !important;
            transition: color 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 20px;
            transition: all 0.2s;
        }
        .btn-primary-custom:hover {
            background-color: #246b43;
            border-color: #246b43;
            color: #fff;
        }
        .btn-outline-custom {
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 20px;
        }
        .btn-outline-custom:hover {
            background-color: var(--primary-color);
            color: #fff;
        }
        main { flex-grow: 1; }
        .hero-section {
            background: linear-gradient(135deg, rgba(46,139,87,0.1), rgba(76,175,80,0.1));
            padding: 80px 0;
            border-radius: 16px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/logo.png" alt="Logo Silaris" onerror="this.style.display='none'">
            Silaris
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Beranda</a>
                </li>
                <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Koordinator'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'laporan.php' ? 'active' : '' ?>" href="laporan.php">Buat Laporan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'daftar-laporan.php' ? 'active' : '' ?>" href="daftar-laporan.php">Daftar Laporan</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'cek-status.php' ? 'active' : '' ?>" href="cek-status.php">Cek Status</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Koordinator'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'rekap-laporan.php' ? 'active' : '' ?>" href="rekap-laporan.php">Rekap Laporan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars(ADMIN_DASHBOARD_URL) ?>" target="_blank" rel="noopener noreferrer">Dashboard</a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-secondary"></i>
                            <div class="text-start">
                                <div class="fw-bold" style="font-size:0.85rem;line-height:1.2;"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($_SESSION['role']) ?></div>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary-custom"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container my-5">

<?php
// Ambil daftar laporan dari API publik
$data     = [];
$errorMsg = null;
$api_url  = API_BASE_URL . '/api/laporan-publik';
$context  = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
$response = @file_get_contents($api_url, false, $context);

if ($response !== false) {
    $resData = json_decode($response, true);
    if (isset($resData['success']) && $resData['success'] && isset($resData['data'])) {
        $data = $resData['data'];
    } else {
        $errorMsg = $resData['message'] ?? 'Data laporan belum bisa dimuat.';
    }
} else {
    $errorMsg = 'Gagal terhubung ke server API.';
}

// Data dummy jika API tidak ada/kosong
if (empty($data)) {
    $errorMsg = null;
    $data = [
        [
            'kode_laporan' => 'SPH-0001',
            'kategori'     => 'Sampah Plastik',
            'petugas'      => 'Petugas A',
            'status'       => 'diproses',
            'label_status' => 'Diproses',
            'dibuat_pada'  => date('Y-m-d H:i:s', strtotime('-1 days')),
        ],
        [
            'kode_laporan' => 'SPH-0002',
            'kategori'     => 'Sampah Organik',
            'petugas'      => 'Petugas B',
            'status'       => 'selesai',
            'label_status' => 'Selesai',
            'dibuat_pada'  => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [
            'kode_laporan' => 'SPH-0003',
            'kategori'     => 'Limbah B3',
            'petugas'      => '',
            'status'       => 'baru',
            'label_status' => 'Baru',
            'dibuat_pada'  => date('Y-m-d H:i:s', strtotime('-1 hours')),
        ],
        [
            'kode_laporan' => 'SPH-0004',
            'kategori'     => 'Sampah Logam & Kaleng',
            'petugas'      => 'Petugas C',
            'status'       => 'selesai',
            'label_status' => 'Selesai',
            'dibuat_pada'  => date('Y-m-d H:i:s', strtotime('-3 days')),
        ],
        [
            'kode_laporan' => 'SPH-0005',
            'kategori'     => 'Sampah Medis & Bahan Infeksius',
            'petugas'      => '',
            'status'       => 'ditolak',
            'label_status' => 'Ditolak',
            'dibuat_pada'  => date('Y-m-d H:i:s', strtotime('-4 days')),
        ],
    ];
}
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4 p-md-5">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-list-ul text-primary me-2"></i>Daftar Laporan Publik
            </h2>
            <p class="text-muted small">Daftar laporan pengaduan sampah dari masyarakat. Identitas pelapor disembunyikan demi privasi.</p>
            <p class="text-muted small mb-0">
                Gunakan tombol <strong>Salin</strong> untuk menyimpan kode laporan, lalu cek status di halaman <em>Cek Status</em>.
            </p>
        </div>

        <?php if ($errorMsg): ?>
            <div class="alert alert-warning d-flex align-items-center rounded-3 p-3" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
                <div class="small"><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 text-secondary text-uppercase" style="font-size:0.8rem;letter-spacing:1px;">Kode Laporan</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size:0.8rem;letter-spacing:1px;">Kategori</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size:0.8rem;letter-spacing:1px;">Petugas</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size:0.8rem;letter-spacing:1px;">Status</th>
                        <th class="py-3 text-secondary text-uppercase" style="font-size:0.8rem;letter-spacing:1px;">Tanggal</th>
                        <th class="py-3 text-secondary text-uppercase text-center" style="font-size:0.8rem;letter-spacing:1px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada laporan publik yang tersedia.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <?php
                                $status = strtolower($row['status'] ?? '');
                                $badgeClass = 'bg-secondary text-white';
                                if ($status === 'baru')     $badgeClass = 'bg-info text-dark';
                                elseif ($status === 'diproses') $badgeClass = 'bg-warning text-dark';
                                elseif ($status === 'selesai')  $badgeClass = 'bg-success text-white';
                                elseif ($status === 'ditolak')  $badgeClass = 'bg-danger text-white';
                                $tgl = isset($row['dibuat_pada']) ? strtotime($row['dibuat_pada']) : time();
                            ?>
                            <tr>
                                <td style="min-width:180px;">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-semibold text-dark font-monospace" style="font-size:0.9rem;">
                                            <?= htmlspecialchars($row['kode_laporan'] ?? '-') ?>
                                        </span>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 copy-kode"
                                                data-kode="<?= htmlspecialchars($row['kode_laporan'] ?? '', ENT_QUOTES) ?>"
                                                title="Salin kode laporan">
                                            <i class="bi bi-clipboard" style="font-size:0.8rem;"></i>
                                            <span style="font-size:0.75rem;">Salin</span>
                                        </button>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
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
                                    <span class="badge rounded-pill <?= $badgeClass ?> px-3 py-2 fw-medium">
                                        <?= htmlspecialchars($row['label_status'] ?? ucfirst($status)) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= formatTanggalIndo($tgl) ?></td>
                                <td class="text-center">
                                    <a href="cek-status.php?kode=<?= urlencode($row['kode_laporan'] ?? '') ?>"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-search me-1"></i>Cek
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>

<script>
document.querySelectorAll('.copy-kode').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var kode   = this.dataset.kode;
        var tombol = this;
        var awal   = tombol.innerHTML;

        function ok() {
            tombol.innerHTML = '<i class="bi bi-clipboard-check" style="font-size:0.8rem;"></i><span style="font-size:0.75rem;">Tersalin!</span>';
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
    });
});
</script>

<?php include 'components/footer.php'; ?>
</body>
</html>