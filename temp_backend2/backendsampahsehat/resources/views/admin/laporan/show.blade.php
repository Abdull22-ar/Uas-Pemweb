@extends('layouts.admin')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">
            Laporan <span class="text-primary font-monospace">{{ $laporanSampah->kode_laporan }}</span>
        </h2>
        <p class="text-muted mb-0"><i class="bi bi-calendar3 me-1"></i> Dilaporkan pada {{ $laporanSampah->created_at->format('d M Y, H:i') }} WIB</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.laporan.edit-status', $laporanSampah) }}" class="btn btn-warning shadow-sm">
            <i class="bi bi-pencil-fill me-2"></i>Update Status
        </a>
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center rounded-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Status Banner --}}
        @php
            $stConfig = match($laporanSampah->status) {
                'baru'     => ['bg'=>'bg-info-subtle', 'color'=>'text-info-emphasis', 'icon'=>'bi-inbox', 'label'=>'Baru Diterima'],
                'diproses' => ['bg'=>'bg-warning-subtle', 'color'=>'text-warning-emphasis', 'icon'=>'bi-gear', 'label'=>'Sedang Diproses'],
                'selesai'  => ['bg'=>'bg-success-subtle', 'color'=>'text-success-emphasis', 'icon'=>'bi-check-circle', 'label'=>'Selesai Ditangani'],
                'ditolak'  => ['bg'=>'bg-danger-subtle', 'color'=>'text-danger-emphasis', 'icon'=>'bi-x-circle', 'label'=>'Ditolak'],
                default    => ['bg'=>'bg-secondary-subtle', 'color'=>'text-secondary-emphasis', 'icon'=>'bi-question-circle', 'label'=>'Tidak Diketahui'],
            };
        @endphp
        <div class="card border-0 shadow-sm rounded-4 mb-4 {{ $stConfig['bg'] }}">
            <div class="card-body p-4 d-flex align-items-center gap-4">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                    <i class="bi {{ $stConfig['icon'] }} fs-2 {{ $stConfig['color'] }}"></i>
                </div>
                <div>
                    <h6 class="text-uppercase fw-bold mb-1 {{ $stConfig['color'] }}" style="letter-spacing: 1px; font-size: 0.8rem;">Status Laporan</h6>
                    <h3 class="fw-bold mb-0 {{ $stConfig['color'] }}">{{ $stConfig['label'] }}</h3>
                    @if($laporanSampah->updated_at != $laporanSampah->created_at)
                        <div class="small mt-1 {{ $stConfig['color'] }} opacity-75">
                            Diperbarui {{ $laporanSampah->updated_at->format('d M Y, H:i') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail Informasi --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white p-4 border-bottom-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-info-circle text-primary me-2"></i>Informasi Laporan</h5>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-6 border-bottom border-end p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1"><i class="bi bi-person me-1"></i>Nama Pelapor</div>
                        <div class="fw-medium text-dark">{{ $laporanSampah->nama_pelapor }}</div>
                    </div>
                    <div class="col-md-6 border-bottom p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1"><i class="bi bi-telephone me-1"></i>Kontak</div>
                        <div class="fw-medium text-dark">{{ $laporanSampah->kontak_pelapor }}</div>
                    </div>
                    <div class="col-md-6 border-bottom border-end p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1"><i class="bi bi-tag me-1"></i>Kategori Sampah</div>
                        <div class="fw-medium text-dark">{{ $laporanSampah->kategori?->nama_kategori ?? '–' }}</div>
                    </div>
                    <div class="col-md-6 border-bottom p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1"><i class="bi bi-shield me-1"></i>Level Risiko</div>
                        <div class="fw-medium text-dark">{{ $laporanSampah->kategori?->label_risiko ?? '–' }}</div>
                    </div>
                    <div class="col-md-6 border-bottom border-end p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1"><i class="bi bi-person-badge me-1"></i>Petugas Penanganan</div>
                        @if($laporanSampah->relationLoaded('petugas') && $laporanSampah->petugas)
                            <div class="fw-medium text-dark">{{ $laporanSampah->petugas->name }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $laporanSampah->petugas->kontak ?? '-' }}</div>
                        @else
                            <div class="fw-medium text-muted">–</div>
                        @endif
                    </div>
                    <div class="col-12 border-bottom p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1"><i class="bi bi-geo-alt me-1"></i>Lokasi Kejadian</div>
                        <div class="fw-medium text-dark">{{ $laporanSampah->lokasi }}</div>
                        @if($laporanSampah->latitude && $laporanSampah->longitude)
                            <div class="small text-danger mt-1">
                                <i class="bi bi-geo me-1"></i>{{ $laporanSampah->latitude }}, {{ $laporanSampah->longitude }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12 p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2"><i class="bi bi-file-text me-1"></i>Deskripsi Masalah</div>
                        <p class="text-dark mb-0" style="white-space: pre-wrap; line-height: 1.6;">{{ $laporanSampah->deskripsi }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Foto Bukti --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white p-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-image text-primary me-2"></i>Foto Bukti</h6>
            </div>
            @if($laporanSampah->foto_url)
                <img src="{{ $laporanSampah->foto_url }}" alt="Foto laporan {{ $laporanSampah->kode_laporan }}" class="img-fluid" style="width: 100%; object-fit: cover;">
            @else
                <div class="p-5 text-center text-muted bg-light">
                    <i class="bi bi-image fs-1 d-block mb-2 opacity-50"></i>
                    Tidak ada foto bukti
                </div>
            @endif
        </div>

        {{-- Catatan Petugas --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text text-warning me-2"></i>Catatan Petugas</h6>
                <a href="{{ route('admin.laporan.edit-status', $laporanSampah) }}#catatanGroup" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">Edit</a>
            </div>
            <div class="card-body p-4">
                @if($laporanSampah->catatan_petugas)
                    <div class="bg-warning bg-opacity-10 border-start border-warning border-4 p-3 rounded-end">
                        <p class="mb-0 text-dark" style="font-size: 0.9rem;">{{ nl2br(e($laporanSampah->catatan_petugas)) }}</p>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-chat-square-quote fs-3 d-block mb-2 opacity-50"></i>
                        <span class="small">Belum ada catatan dari petugas.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Peta Lokasi --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white p-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-map text-success me-2"></i>Peta Lokasi</h6>
            </div>
            <div class="card-body p-0">
                @if($laporanSampah->latitude && $laporanSampah->longitude)
                    <div id="map" style="height: 250px; width: 100%;"></div>
                @else
                    <div class="p-4 text-center text-muted bg-light">
                        <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-50"></i>
                        Koordinat lokasi tidak tersedia
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($laporanSampah->latitude && $laporanSampah->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var lat = {{ $laporanSampah->latitude }};
    var lng = {{ $laporanSampah->longitude }};
    
    var map = L.map('map').setView([lat, lng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    L.marker([lat, lng]).addTo(map)
        .bindPopup('<b>Lokasi Kejadian</b><br>{{ $laporanSampah->lokasi }}')
        .openPopup();
});
</script>
@endif

@endsection
