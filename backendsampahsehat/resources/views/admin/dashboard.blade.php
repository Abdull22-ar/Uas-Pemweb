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

{{-- ── STAT CARDS ───────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'baru') }}" class="text-decoration-none">
            <div class="card h-100 border-primary border-opacity-25 shadow-sm hover-elevate">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Laporan Baru</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $totalBaru }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                            <i class="bi bi-inbox fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'diproses') }}" class="text-decoration-none">
            <div class="card h-100 border-warning border-opacity-25 shadow-sm hover-elevate">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Sedang Diproses</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $totalDiproses }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning">
                            <i class="bi bi-gear-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.laporan.by-status', 'selesai') }}" class="text-decoration-none">
            <div class="card h-100 border-success border-opacity-25 shadow-sm hover-elevate">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Selesai</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $totalSelesai }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.laporan.by-risiko', 'tinggi') }}" class="text-decoration-none">
            <div class="card h-100 border-danger border-opacity-25 shadow-sm hover-elevate">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Risiko Tinggi</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $totalRisikoTinggi }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
    .hover-elevate { transition: transform 0.2s, box-shadow 0.2s; }
    .hover-elevate:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
</style>

{{-- ── SECONDARY STATS ─────────────────────────────────────── --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm p-4 d-flex flex-row align-items-center gap-3">
            <div class="bg-purple text-purple bg-opacity-10 p-3 rounded-3" style="color:#6f42c1; background-color:rgba(111,66,193,0.1)">
                <i class="bi bi-collection-fill fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $totalLaporan }}</h3>
                <span class="text-muted small">Total Laporan</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm p-4 d-flex flex-row align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-danger">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $totalBelumSelesai }}</h3>
                <span class="text-muted small">Belum Selesai</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm p-4 d-flex flex-row align-items-center gap-3">
            <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success">
                <i class="bi bi-pie-chart-fill fs-4"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">{{ $persentaseSelesai }}%</h3>
                <span class="text-muted small">Tingkat Penyelesaian</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        {{-- ── LAPORAN TERBARU ─────────────────────────────────── --}}
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="bi bi-clock text-info me-2"></i>Laporan Terbaru</h6>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Kode</th>
                            <th>Pelapor</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($laporanTerbaru as $l)
                        <tr>
                            <td class="ps-4">
                                <span class="font-monospace text-primary small">{{ $l->kode_laporan }}</span>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ Str::limit($l->nama_pelapor, 20) }}</div>
                                <div class="text-muted small"><i class="bi bi-geo-alt text-danger me-1"></i>{{ Str::limit($l->lokasi, 25) }}</div>
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
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada laporan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        {{-- ── LAPORAN PER KATEGORI ────────────────────────────── --}}
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="bi bi-tags-fill" style="color:#6f42c1;"></i> Laporan per Kategori</h6>
            </div>
            <div class="card-body">
                @forelse($laporanPerKategori as $kat)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-medium text-dark">{{ $kat->nama_kategori }}</span>
                        <span class="badge rounded-pill bg-light text-dark border">{{ $kat->laporan_sampah_count }}</span>
                    </div>
                    @php
                        $pct = $totalLaporan > 0 ? round($kat->laporan_sampah_count / $totalLaporan * 100) : 0;
                        $barColor = match($kat->level_risiko) {
                            'tinggi' => 'bg-danger',
                            'sedang' => 'bg-warning',
                            default  => 'bg-success',
                        };
                    @endphp
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">Belum ada data kategori</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
