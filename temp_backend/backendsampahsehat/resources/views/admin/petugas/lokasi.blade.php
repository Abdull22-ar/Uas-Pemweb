@extends('layouts.admin')

@section('title', 'Lokasi Saya')
@section('page-title', 'Lokasi Saya')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Lokasi Petugas</h2>
        <p class="text-muted mb-0">Atur lokasi Anda agar admin dapat melihat posisi petugas di peta.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-success me-2"></i>Pilih Lokasi</h6>
            </div>
            <div class="card-body p-1">
                <div id="lokasiMap" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i>Update Lokasi</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.lokasi.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Latitude</label>
                        <input type="text" id="latitude" name="latitude"
                               class="form-control @error('latitude') is-invalid @enderror"
                               value="{{ old('latitude', auth()->user()->latitude) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Longitude</label>
                        <input type="text" id="longitude" name="longitude"
                               class="form-control @error('longitude') is-invalid @enderror"
                               value="{{ old('longitude', auth()->user()->longitude) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Nama Lokasi</label>
                        <input type="text" id="lokasi" name="lokasi"
                               class="form-control @error('lokasi') is-invalid @enderror"
                               value="{{ old('lokasi', auth()->user()->lokasi) }}"
                               placeholder="Misal: Kantor Kecamatan Kebomas">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-check-lg me-2"></i>Simpan Lokasi
                    </button>
                </form>
                @if(auth()->user()->latitude && auth()->user()->longitude)
                <div class="mt-3 p-3 bg-success bg-opacity-10 rounded-3 text-center">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span class="small text-success fw-semibold ms-1">Lokasi sudah disimpan</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    #lokasiMap { z-index: 1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var defaultLat = {{ auth()->user()->latitude ?? '-7.2575' }};
    var defaultLng = {{ auth()->user()->longitude ?? '112.7521' }};

    var map = L.map('lokasiMap').setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    var marker;
    if (defaultLat && defaultLng && document.getElementById('latitude').value) {
        marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        marker.on('dragend', function() {
            updateCoords(marker.getLatLng());
        });
    }

    map.on('click', function(e) {
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function() {
                updateCoords(marker.getLatLng());
            });
        }
        updateCoords(e.latlng);
    });

    function updateCoords(latlng) {
        document.getElementById('latitude').value = latlng.lat.toFixed(6);
        document.getElementById('longitude').value = latlng.lng.toFixed(6);
        // Reverse geocode sederhana
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latlng.lat + '&lon=' + latlng.lng + '&accept-language=id')
            .then(r => r.json())
            .then(d => {
                if (d.display_name && !document.getElementById('lokasi').value) {
                    document.getElementById('lokasi').value = d.display_name.split(', ').slice(0, 3).join(', ');
                }
            })
            .catch(() => {});
    }
});
</script>
@endsection