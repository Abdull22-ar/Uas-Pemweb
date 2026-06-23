<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Silaris - Sistem Pengelolaan Laporan Sampah">
    <title>@yield('title', 'Dashboard') – Silaris</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-color: #2e8b57;
            --primary-hover: #246b43;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
        }

        /* ─── SIDEBAR ─────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            border-right: 1px solid #e0e0e0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-brand img {
            height: 35px;
            border-radius: 4px;
        }

        .brand-text { font-size: 18px; font-weight: 700; color: var(--primary-color); line-height: 1.2; }
        .brand-sub  { font-size: 12px; color: #6c757d; }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 15px 10px;
        }

        .nav-section-label {
            font-size: 11px;
            font-weight: 700;
            color: #adb5bd;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 15px 5px;
            margin-top: 10px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 15px;
            color: #495057;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 4px;
        }

        .sidebar-nav .nav-link i { font-size: 18px; }

        .sidebar-nav .nav-link:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }

        .sidebar-nav .nav-link.active {
            background-color: rgba(46, 139, 87, 0.1);
            color: var(--primary-color);
            font-weight: 600;
        }

        /* ─── TOPBAR ───────────────────────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0; left: var(--sidebar-width); right: 0;
            height: 65px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 1030;
            transition: left 0.3s ease;
        }

        .topbar-title {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }

        /* ─── MAIN CONTENT ─────────────────────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding-top: 65px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-content {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title { margin: 0; font-weight: 600; font-size: 16px; }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* ─── MOBILE RESPONSIVENESS ────────────────────────────────── */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #212529;
            cursor: pointer;
            margin-right: 15px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
        }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .mobile-toggle { display: inline-block; }
            .main-wrapper { margin-left: 0; }
            .topbar { left: 0; padding: 0 15px; }
            .page-content { padding: 20px 15px; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <a href="#" class="sidebar-brand">
        <img src="{{ asset('logo.png') }}" alt="Logo Silaris">
        <div>
            <div class="brand-text">Silaris</div>
            <div class="brand-sub">Panel {{ str_contains(strtolower(Auth::user()->email), 'petugas') ? 'Petugas' : 'Admin' }}</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Manajemen Laporan
        </a>

        <div class="nav-section-label">Master Data</div>
        <a href="{{ route('admin.kategori.index') }}" class="nav-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Kategori Sampah
        </a>
    </nav>
</aside>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
        <div class="d-flex align-items-center">
            <button class="mobile-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
        </div>
        
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-bold" style="font-size: 13px; line-height: 1;">{{ Auth::user()->name }}</div>
                    <div class="text-muted" style="font-size: 11px;">{{ str_contains(strtolower(Auth::user()->email), 'petugas') ? 'Petugas' : 'Admin' }}</div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toggle   = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');

    if(toggle) {
        toggle.addEventListener('click', () => {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        });
    }

    if(overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }
</script>
</body>
</html>
