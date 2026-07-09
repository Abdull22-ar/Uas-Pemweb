<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

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
