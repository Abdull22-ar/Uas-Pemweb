<?php include 'components/navbar.php'; ?>

<div class="hero-section text-center px-4">
    <div class="container">
        <div class="mb-4 d-flex justify-content-center">
            <div class="bg-white p-3 rounded-circle shadow-sm" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-tree-fill text-success" style="font-size: 2.5rem;"></i>
            </div>
        </div>
        
        <h1 class="display-4 fw-bolder text-dark mb-3">
            Selamat Datang di <span style="color: var(--primary-color);">Silaris</span>
        </h1>
        
        <p class="lead text-muted mx-auto mb-5" style="max-width: 600px;">
            Layanan SampahSehat. Laporkan penumpukan atau masalah sampah di sekitarmu dengan mudah, cepat, dan transparan demi lingkungan yang lebih asri.
        </p>

        <div class="d-flex justify-content-center gap-3">
            <a href="laporan.php" class="btn btn-primary-custom btn-lg shadow-sm">
                <i class="bi bi-pencil-square me-2"></i>Buat Laporan Baru
            </a>
            <a href="cek-status.php" class="btn btn-outline-custom btn-lg bg-white">
                <i class="bi bi-search me-2"></i>Cek Status
            </a>
        </div>
    </div>
</div>

<div class="row text-center mt-5">
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <i class="bi bi-geo-alt-fill text-danger mb-3" style="font-size: 2.5rem;"></i>
            <h5 class="fw-bold">1. Tentukan Lokasi</h5>
            <p class="text-muted small">Pilih titik lokasi penumpukan sampah di peta dengan akurat.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <i class="bi bi-camera-fill text-primary mb-3" style="font-size: 2.5rem;"></i>
            <h5 class="fw-bold">2. Upload Bukti</h5>
            <p class="text-muted small">Ambil gambar atau unggah foto sebagai bukti laporan valid.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <i class="bi bi-check2-circle text-success mb-3" style="font-size: 2.5rem;"></i>
            <h5 class="fw-bold">3. Pantau Status</h5>
            <p class="text-muted small">Lacak laporanmu hingga ditindaklanjuti oleh petugas terkait.</p>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>