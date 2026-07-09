@extends('layouts.admin')

@section('title', 'Pemantauan Petugas')
@section('page-title', 'Pemantauan Petugas')

@section('content')

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Pemantauan Petugas Lapangan</h2>
        <p class="text-muted mb-0">Pantau lokasi dan penugasan petugas secara real-time</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-primary rounded-pill px-3 py-2"><i class="bi bi-people-fill me-1"></i>{{ $totalPetugas }} Petugas</span>
        <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-gear me-1"></i>{{ $totalDiproses }} Diproses</span>
        <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-person-check me-1"></i>{{ $petugasAktif }} Aktif</span>
    </div>
</div>

{{-- MAP --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-success me-2"></i>Peta Sebaran Petugas</h6>
        <div class="d-flex gap-3 align-items-center">
            <div class="d-flex gap-2" id="mapLegend">
                <span class="small"><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:#198754;"></span>Rendah</span>
                <span class="small"><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:#ffc107;"></span>Sedang</span>
                <span class="small"><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:#dc3545;"></span>Tinggi</span>
            </div>
            <div class="form-check form-switch mb-0 d-flex align-items-center gap-1" style="font-size:13px;">
                <input class="form-check-input" type="checkbox" id="tampilkanLaporan" checked>
                <label class="form-check-label text-muted small" for="tampilkanLaporan">Laporan</label>
            </div>
        </div>
    </div>
    <div id="petugasMap" style="height: 500px; width: 100%;"></div>
</div>

{{-- Laporan Diproses per Level Risiko --}}
<div class="row g-4">
    @php
        $risikoConfig = [
            'rendah' => ['label' => 'Rendah', 'color' => 'success', 'border' => '#198754', 'icon' => 'bi-shield-check'],
            'sedang' => ['label' => 'Sedang', 'color' => 'warning', 'border' => '#ffc107', 'icon' => 'bi-shield-exclamation'],
            'tinggi' => ['label' => 'Tinggi', 'color' => 'danger', 'border' => '#dc3545', 'icon' => 'bi-shield-fill'],
        ];
    @endphp

    @foreach (['rendah', 'sedang', 'tinggi'] as $level)
        @php $cfg = $risikoConfig[$level]; $data = $kelompok[$level]; @endphp
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-start border-4" style="border-left-color: {{ $cfg['border'] }} !important;">
                    <h6 class="fw-bold mb-0"><i class="bi {{ $cfg['icon'] }} text-{{ $cfg['color'] }} me-2"></i>{{ $cfg['label'] }}</h6>
                    <span class="badge bg-{{ $cfg['color'] }} rounded-pill">{{ $data['total'] }} diproses</span>
                </div>
                <div class="card-body p-0">
                    @if($data['total'] > 0)
                        @foreach($data['kategori'] as $kat)
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark small">{{ $kat['nama_kategori'] }}</span>
                                <span class="badge bg-{{ $cfg['color'] }} bg-opacity-10 text-{{ $cfg['color'] }} border border-{{ $cfg['color'] }} border-opacity-25 px-2">{{ $kat['total'] }} laporan</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach($kat['petugas_list'] as $petugas)
                                    <span class="badge bg-light text-dark border px-2 py-1 small">
                                        <i class="bi bi-person-badge me-1 text-{{ $cfg['color'] }}"></i>{{ $petugas }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="small text-muted" style="font-size:11px;">
                                @foreach($kat['laporan'] as $l)
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="font-monospace">{{ $l->kode_laporan }}</span>
                                    <span>{{ $l->petugas?->name ?? '-' }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                            Tidak ada laporan diproses untuk level {{ $cfg['label'] }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
    #petugasMap { z-index: 1; }
    .leaflet-container { border-radius: 0 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('petugasMap', {
        zoomControl: true,
        attributionControl: false,
    }).setView([-7.2575, 112.7521], 11);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 18,
    }).addTo(map);

    L.control.attribution({ position: 'bottomright', prefix: false }).addTo(map);

    function getRisikoBg(r) {
        switch(r) { case 'tinggi': return '#dc3545'; case 'sedang': return '#ffc107'; default: return '#198754'; }
    }

    function getStatusColor(s) {
        switch(s) { case 'baru': return '#0d6efd'; case 'diproses': return '#ffc107'; case 'selesai': return '#198754'; case 'ditolak': return '#dc3545'; default: return '#6c757d'; }
    }

    // ── Petugas markers ────────────────────────────────────────────
    var petugasData = @json($petugasMapData);
    var petugasMarkers = [];

    petugasData.forEach(function(p) {
        var color = getRisikoBg(p.risiko);
        var icon = L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:32px;height:32px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:bold;">' + p.nama.charAt(0).toUpperCase() + '</div>',
            iconSize: [38, 38],
            iconAnchor: [19, 19],
        });
        var m = L.marker([p.lat, p.lng], { icon: icon }).addTo(map)
            .bindPopup(
                '<div style="min-width:180px;">' +
                '<h6 class="fw-bold mb-1" style="font-size:14px;color:#2e8b57;"><i class="bi bi-person-badge"></i> ' + p.nama + '</h6>' +
                '<div class="small mb-1"><span class="badge rounded-pill" style="background:' + color + ';color:#fff;">' + p.risiko.charAt(0).toUpperCase() + p.risiko.slice(1) + '</span></div>' +
                (p.lokasi ? '<div class="small text-muted mb-1"><i class="bi bi-tools"></i> ' + p.lokasi + '</div>' : '') +
                (p.kontak ? '<div class="small text-muted"><i class="bi bi-telephone"></i> ' + p.kontak + '</div>' : '') +
                '</div>'
            );
        petugasMarkers.push(m);
    });

    // ── Laporan markers ─────────────────────────────────────────────
    var laporanData = @json($laporanMapData);
    var laporanMarkers = [];
    var laporanLayer = L.layerGroup().addTo(map);

    laporanData.forEach(function(l) {
        var color = getRisikoBg(l.risiko);
        var icon = L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
            iconSize: [18, 18],
            iconAnchor: [9, 9],
        });
        var m = L.marker([l.lat, l.lng], { icon: icon }).addTo(map)
            .bindPopup(
                '<div style="min-width:180px;">' +
                '<h6 class="fw-bold mb-1" style="font-size:13px;color:#2e8b57;">' + l.kode + '</h6>' +
                '<div class="small text-muted mb-1"><i class="bi bi-tag"></i> ' + l.kategori + '</div>' +
                '<div class="small text-muted mb-1"><i class="bi bi-geo-alt"></i> ' + l.lokasi + '</div>' +
                (l.petugas ? '<div class="small text-muted mb-1"><i class="bi bi-person-badge"></i> ' + l.petugas + '</div>' : '') +
                '<span class="badge rounded-pill" style="background:' + getStatusColor(l.status) + ';">' + l.label_status + '</span>' +
                '</div>'
            );
        laporanMarkers.push(m);
        laporanLayer.addLayer(m);
    });

    // ── Toggle laporan markers ──────────────────────────────────────
    document.getElementById('tampilkanLaporan').addEventListener('change', function() {
        if (this.checked) map.addLayer(laporanLayer);
        else map.removeLayer(laporanLayer);
    });

    // ── Fit bounds ──────────────────────────────────────────────────
    var allGroups = L.featureGroup([].concat(petugasMarkers, laporanMarkers));
    if (allGroups.getLayers().length > 0) {
        map.fitBounds(allGroups.getBounds().pad(0.08));
    }
});
</script>
@endsection
