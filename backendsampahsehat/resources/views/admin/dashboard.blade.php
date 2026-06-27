@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Ringkasan Sistem</h2>
        <p class="text-muted mb-0">Pantau laporan sampah secara real-time</p>
    </div>
    <a href="{{ route('admin.laporan.index') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-list-ul me-2"></i>Semua Laporan
    </a>
</div>

{{-- ── STAT CARDS ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'baru') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary flex-shrink-0">
                        <i class="bi bi-inbox fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="text-muted mb-0 small text-uppercase fw-semibold">Baru</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalBaru }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'diproses') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning flex-shrink-0">
                        <i class="bi bi-gear-fill fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="text-muted mb-0 small text-uppercase fw-semibold">Diproses</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalDiproses }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'selesai') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success flex-shrink-0">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="text-muted mb-0 small text-uppercase fw-semibold">Selesai</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalSelesai }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'ditolak') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger flex-shrink-0">
                        <i class="bi bi-x-circle-fill fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="text-muted mb-0 small text-uppercase fw-semibold">Ditolak</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $totalDitolak }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 flex-shrink-0" style="color:#6f42c1; background-color:rgba(111,66,193,0.1)">
                    <i class="bi bi-collection-fill fs-4"></i>
                </div>
                <div class="min-w-0">
                    <h6 class="text-muted mb-0 small text-uppercase fw-semibold">Total</h6>
                    <h3 class="mb-0 fw-bold text-dark">{{ $totalLaporan }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 p-3 rounded-3 text-info flex-shrink-0">
                    <i class="bi bi-check2-all fs-4"></i>
                </div>
                <div class="min-w-0">
                    <h6 class="text-muted mb-0 small text-uppercase fw-semibold">Selesai</h6>
                    <h3 class="mb-0 fw-bold text-dark">{{ $persentaseSelesai }}%</h3>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── MAP ─────────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-geo-alt-fill text-success me-2"></i>Peta Sebaran Laporan
                    @if($petugasMapData->isNotEmpty())
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 ms-2 small fw-normal" id="petugasBadge">
                            <i class="bi bi-people-fill me-1"></i>{{ $petugasMapData->count() }} petugas
                        </span>
                    @endif
                </h6>
                <div class="d-flex gap-3 align-items-center">
                    <div class="d-none d-md-flex gap-3">
                        <span class="small"><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:#198754;"></span>Rendah</span>
                        <span class="small"><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:#ffc107;"></span>Sedang</span>
                        <span class="small"><span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:#dc3545;"></span>Tinggi</span>
                    </div>
                    @if($petugasMapData->isNotEmpty())
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-1" style="font-size:13px;">
                        <input class="form-check-input" type="checkbox" id="tampilkanPetugas" checked>
                        <label class="form-check-label text-muted small" for="tampilkanPetugas">Petugas</label>
                    </div>
                    @endif
                    <select id="mapFilterStatus" class="form-select form-select-sm" style="width:auto;">
                        <option value="all">Semua Status</option>
                        <option value="baru">Baru</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
            </div>
            <div id="dashboardMap" style="height: 420px; width: 100%;"></div>
        </div>
    </div>
</div>

{{-- ── KATEGORI PER RISIKO + CHART HARIAN ─────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-tags-fill" style="color:#6f42c1;"></i> Kategori per Level Risiko</h6>
            </div>
            <div class="card-body">
                @php
                    $risikoData = [
                        'rendah' => ['label' => 'Rendah', 'color' => 'success', 'icon' => 'bi-shield-check'],
                        'sedang' => ['label' => 'Sedang', 'color' => 'warning', 'icon' => 'bi-shield-exclamation'],
                        'tinggi' => ['label' => 'Tinggi', 'color' => 'danger', 'icon' => 'bi-shield-fill'],
                    ];

                    $userLevel = auth()->user()->role === 'petugas' && auth()->user()->spesialis_risiko
                        ? [auth()->user()->spesialis_risiko]
                        : ['rendah', 'sedang', 'tinggi'];
                @endphp

                <ul class="nav nav-pills mb-3 gap-2" role="tablist" id="risikoTab">
                    @foreach ($userLevel as $level)
                        @php $cfg = $risikoData[$level]; @endphp
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }} d-flex align-items-center gap-2 py-2 px-3"
                                    data-bs-toggle="pill"
                                    data-bs-target="#risiko-{{ $level }}"
                                    type="button" role="tab">
                                <i class="bi {{ $cfg['icon'] }} text-{{ $cfg['color'] }}"></i>
                                <span class="small fw-semibold">{{ $cfg['label'] }}</span>
                                <span class="badge bg-{{ $cfg['color'] }} rounded-pill">{{ $kategoriPerRisiko[$level]->count() }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach ($userLevel as $level)
                        @php $cfg = $risikoData[$level]; @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="risiko-{{ $level }}" role="tabpanel">
                            @php $items = $kategoriPerRisiko[$level]; @endphp
                            @if($items->isNotEmpty())
                                <div class="row g-2">
                                    @foreach($items as $kat)
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border {{ $loop->first ? 'border-' . $cfg['color'] : '' }}" style="{{ $loop->first ? 'border-width:2px;' : '' }}">
                                                <div>
                                                    <div class="fw-medium small text-dark">{{ $kat->nama_kategori }}</div>
                                                    @if($kat->deskripsi)
                                                        <div class="text-muted" style="font-size:11px;">{{ Str::limit($kat->deskripsi, 40) }}</div>
                                                    @endif
                                                </div>
                                                <div class="text-center flex-shrink-0 ms-2">
                                                    <div class="fw-bold fs-5 text-{{ $cfg['color'] }}">{{ $kat->laporan_sampah_count }}</div>
                                                    <div class="text-muted" style="font-size:10px;line-height:1;">laporan</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted small">Tidak ada kategori {{ $cfg['label'] }}.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Laporan Harian (7 Hari)</h6>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="harianChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── LAPORAN TERBARU + PENANGANAN HARIAN ────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock text-info me-2"></i>Laporan Terbaru</h6>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-2 small">Kode</th>
                            <th class="py-2 small">Pelapor / Lokasi</th>
                            <th class="py-2 small">Status</th>
                            <th class="text-end pe-4 py-2 small">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($laporanTerbaru as $l)
                        <tr>
                            <td class="ps-4">
                                <span class="font-monospace text-primary small">{{ $l->kode_laporan }}</span>
                            </td>
                            <td>
                                <div class="fw-medium text-dark small">{{ Str::limit($l->nama_pelapor, 18) }}</div>
                                <div class="text-muted small"><i class="bi bi-geo-alt text-danger me-1"></i>{{ Str::limit($l->lokasi, 22) }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($l->status) {
                                        'baru'     => 'bg-info text-dark',
                                        'diproses' => 'bg-warning text-dark',
                                        'selesai'  => 'bg-success',
                                        'ditolak'  => 'bg-danger',
                                        default    => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeClass }}">{{ ucfirst($l->status) }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.laporan.show', $l) }}" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted small">Belum ada laporan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        @if(auth()->user()->role === 'admin')
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-check2-all text-success me-2"></i>Penanganan Selesai per Risiko (7 Hari)</h6>
            </div>
            <div class="card-body p-0">
                @php
                    $tglLabels = [];
                    for ($i = 6; $i >= 0; $i--) {
                        $tglLabels[] = now()->subDays($i)->format('Y-m-d');
                    }
                    $risikoLevels = ['rendah', 'sedang', 'tinggi'];
                    $risikoNames = ['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'];
                    $risikoColors = ['rendah' => 'success', 'sedang' => 'warning', 'tinggi' => 'danger'];
                @endphp
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-2 small">Tanggal</th>
                                @foreach($risikoLevels as $rl)
                                    <th class="text-center py-2 small text-{{ $risikoColors[$rl] }}">{{ $risikoNames[$rl] }}</th>
                                @endforeach
                                <th class="text-center pe-4 py-2 small">Total</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($tglLabels as $tgl)
                                @php
                                    $rowData = [];
                                    $rowTotal = 0;
                                    foreach ($risikoLevels as $rl) {
                                        $val = $penangananHarian->firstWhere(fn($d) => $d->tgl === $tgl && $d->level_risiko === $rl);
                                        $rowData[$rl] = $val ? (int) $val->total : 0;
                                        $rowTotal += $rowData[$rl];
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-4 py-2 small text-muted">{{ \Carbon\Carbon::parse($tgl)->format('d M') }}</td>
                                    @foreach($risikoLevels as $rl)
                                        <td class="text-center py-2">
                                            @if($rowData[$rl] > 0)
                                                <span class="badge bg-{{ $risikoColors[$rl] }} bg-opacity-10 text-{{ $risikoColors[$rl] }} border border-{{ $risikoColors[$rl] }} border-opacity-25 px-2 py-1">{{ $rowData[$rl] }}</span>
                                            @else
                                                <span class="text-muted" style="font-size:12px;">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center pe-4 py-2 fw-semibold small">{{ $rowTotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ── RISIKO TINGGI AKTIF (Admin only, jika ada) ─────────────── --}}
@if(auth()->user()->role === 'admin' && $laporanRisikoTinggiAktif->isNotEmpty())
<div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
    <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
        <h6 class="fw-bold mb-0 text-danger">Laporan Risiko Tinggi Aktif</h6>
        <span class="badge bg-danger rounded-pill ms-1">{{ $laporanRisikoTinggiAktif->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-2 small">Kode</th>
                    <th class="py-2 small">Pelapor</th>
                    <th class="py-2 small">Kategori</th>
                    <th class="py-2 small">Status</th>
                    <th class="text-end pe-4 py-2 small">Aksi</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @foreach($laporanRisikoTinggiAktif as $l)
                <tr>
                    <td class="ps-4">
                        <span class="font-monospace text-danger small fw-semibold">{{ $l->kode_laporan }}</span>
                    </td>
                    <td class="small">{{ Str::limit($l->nama_pelapor, 20) }}</td>
                    <td class="small">{{ $l->kategori?->nama_kategori ?? '-' }}</td>
                    <td>
                        @php
                            $badgeClass = match($l->status) {
                                'baru'     => 'bg-info text-dark',
                                'diproses' => 'bg-warning text-dark',
                                default    => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge rounded-pill {{ $badgeClass }}">{{ ucfirst($l->status) }}</span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('admin.laporan.show', $l) }}" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── LAPORAN DIPROSES DENGAN PETUGAS (Admin) ──────────────────── --}}
@if(auth()->user()->role === 'admin' && $laporanDiprosesPetugas->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-people-fill text-warning me-2"></i>Penanganan Aktif
            <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $laporanDiprosesPetugas->count() }}</span>
        </h6>
        <a href="{{ route('admin.pemantauan.petugas') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i>Pemantauan
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-2 small">Kode</th>
                    <th class="py-2 small">Pelapor</th>
                    <th class="py-2 small">Risiko</th>
                    <th class="py-2 small">Kategori</th>
                    <th class="py-2 small">Petugas</th>
                    <th class="text-end pe-4 py-2 small">Aksi</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @foreach($laporanDiprosesPetugas as $l)
                @php
                    $color = match($l->kategori?->level_risiko) {
                        'tinggi' => 'danger', 'sedang' => 'warning', default => 'success'
                    };
                @endphp
                <tr>
                    <td class="ps-4"><span class="font-monospace small text-primary">{{ $l->kode_laporan }}</span></td>
                    <td class="small">{{ Str::limit($l->nama_pelapor, 18) }}</td>
                    <td>
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 small px-2">
                            {{ $l->kategori?->label_risiko ?? '-' }}
                        </span>
                    </td>
                    <td class="small">{{ $l->kategori?->nama_kategori ?? '-' }}</td>
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1 small">
                            <i class="bi bi-person-badge text-{{ $color }} me-1"></i>{{ $l->petugas?->name ?? '-' }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('admin.laporan.show', $l) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<style>
    .hover-elevate { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-elevate:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
    .min-w-0 { min-width: 0; }
    #dashboardMap { z-index: 1; }
    .leaflet-container { border-radius: 0 !important; }
    .nav-pills .nav-link { font-size: 0.85rem; }
    .nav-pills .nav-link.active { background-color: var(--primary-color); color: #fff !important; }
    .nav-pills .nav-link.active i { color: #fff !important; }
    .nav-pills .nav-link.active .badge { background-color: rgba(255,255,255,0.3) !important; color: #fff !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('dashboardMap', {
        zoomControl: true,
        attributionControl: false,
    }).setView([-7.2575, 112.7521], 11);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
        maxZoom: 18,
    }).addTo(map);

    L.control.attribution({ position: 'bottomright', prefix: false }).addTo(map);

    var laporanData = @json($laporanMapData);

    function getColor(v, type) {
        if (type === 'risiko') {
            switch(v) { case 'tinggi': return '#dc3545'; case 'sedang': return '#ffc107'; default: return '#198754'; }
        }
        switch(v) { case 'baru': return '#0d6efd'; case 'diproses': return '#ffc107'; case 'selesai': return '#198754'; case 'ditolak': return '#dc3545'; default: return '#6c757d'; }
    }

    function makeIcon(color, big) {
        var s = big ? 14 : 10;
        return L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:' + s + 'px;height:' + s + 'px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.25);"></div>',
            iconSize: [s + 4, s + 4],
            iconAnchor: [(s + 4)/2, (s + 4)/2],
        });
    }

    var markers = [];
    laporanData.forEach(function(l) {
        var color = getColor(l.risiko, 'risiko');
        var m = L.marker([l.lat, l.lng], { icon: makeIcon(color, l.risiko === 'tinggi') })
            .addTo(map)
            .bindPopup(
                '<div style="min-width:180px;font-family:Inter,sans-serif;">' +
                '<h6 style="font-weight:700;margin:0 0 4px;font-size:13px;color:#2e8b57;">' + l.kode + '</h6>' +
                '<div style="font-size:12px;color:#495057;margin-bottom:2px;"><i class="bi bi-person" style="color:#6c757d;"></i> ' + l.pelapor + '</div>' +
                '<div style="font-size:12px;color:#495057;margin-bottom:2px;"><i class="bi bi-geo-alt" style="color:#dc3545;"></i> ' + l.lokasi + '</div>' +
                '<div style="font-size:12px;color:#495057;margin-bottom:4px;"><i class="bi bi-tag" style="color:#6f42c1;"></i> ' + l.kategori + '</div>' +
                '<div style="display:flex;gap:4px;margin-top:4px;">' +
                '<span style="background:' + getColor(l.status, 'status') + ';color:#fff;padding:2px 10px;border-radius:12px;font-size:11px;">' + l.label_status + '</span>' +
                '<span style="background:#6c757d;color:#fff;padding:2px 10px;border-radius:12px;font-size:11px;">' + l.label_risiko + '</span>' +
                '</div></div>'
            );
        m._d = l;
        markers.push(m);
    });

    // ── Petugas Markers ────────────────────────────────────────────
    var petugasData = @json($petugasMapData);
    var petugasMarkers = [];
    var petugasLayer = L.layerGroup().addTo(map);

    function getRisikoBg(r) {
        switch(r) { case 'tinggi': return '#dc3545'; case 'sedang': return '#ffc107'; default: return '#198754'; }
    }

    petugasData.forEach(function(p) {
        var color = getRisikoBg(p.risiko);
        var icon = L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:bold;">' + p.nama.charAt(0).toUpperCase() + '</div>',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
        var m = L.marker([p.lat, p.lng], { icon: icon })
            .bindPopup(
                '<div style="min-width:160px;font-family:Inter,sans-serif;">' +
                '<h6 style="font-weight:700;margin:0 0 4px;font-size:14px;color:#2e8b57;"><i class="bi bi-person-badge"></i> ' + p.nama + '</h6>' +
                '<div style="font-size:12px;color:#495057;"><span class="badge rounded-pill" style="background:' + color + ';color:#fff;">' + p.risiko.charAt(0).toUpperCase() + p.risiko.slice(1) + '</span></div>' +
                (p.lokasi ? '<div style="font-size:12px;color:#6c757d;margin-top:4px;"><i class="bi bi-geo-alt"></i> ' + p.lokasi + '</div>' : '') +
                '</div>'
            );
        petugasMarkers.push(m);
        petugasLayer.addLayer(m);
    });

    document.getElementById('tampilkanPetugas').addEventListener('change', function() {
        if (this.checked) {
            map.addLayer(petugasLayer);
        } else {
            map.removeLayer(petugasLayer);
        }
    });

    // ── Fit bounds to all markers ────────────────────────────────────
    var allGroups = L.featureGroup([].concat(markers, petugasMarkers));
    if (allGroups.getLayers().length > 0) {
        map.fitBounds(allGroups.getBounds().pad(0.08));
    }

    document.getElementById('mapFilterStatus').addEventListener('change', function() {
        var v = this.value;
        markers.forEach(function(m) {
            if (v === 'all' || m._d.status === v) { if (!map.hasLayer(m)) map.addLayer(m); }
            else { map.removeLayer(m); }
        });
    });

    // Chart Harian
    var ctx = document.getElementById('harianChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Laporan Masuk',
                data: @json($chartData),
                backgroundColor: 'rgba(46,139,87,0.6)',
                borderColor: 'rgba(46,139,87,1)',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });
});
</script>
@endsection