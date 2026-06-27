@extends('layouts.admin')

@section('title', 'Manajemen Laporan')
@section('page-title', 'Manajemen Laporan')

@section('content')

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Laporan Sampah</h2>
        <p class="text-muted mb-0">Kelola dan tindaklanjuti semua laporan yang masuk</p>
    </div>
    <div>
        <a href="http://localhost:8080/laporan.php" target="_blank" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-box-arrow-up-right me-2"></i>Form Publik
        </a>
    </div>
</div>

{{-- ── FILTER BAR ─────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Cari</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Kode, nama, lokasi..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Kategori</label>
                <select name="kategori_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                            {{ Str::limit($kat->nama_kategori, 15) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="baru"     {{ request('status') == 'baru'     ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai"  {{ request('status') == 'selesai'  ? 'selected' : '' }}>Selesai</option>
                    <option value="ditolak"  {{ request('status') == 'ditolak'  ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Level Risiko</label>
                <select name="level_risiko" class="form-select">
                    <option value="">Semua Level</option>
                    <option value="rendah" {{ request('level_risiko') == 'rendah' ? 'selected' : '' }}>Rendah</option>
                    <option value="sedang" {{ request('level_risiko') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="tinggi" {{ request('level_risiko') == 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-2"></i>Filter
                </button>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-light border">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>

        {{-- Active filter badges --}}
        @php
            $activeFilters = array_filter([
                request('search')       ? 'Cari: "'.request('search').'"' : null,
                request('status')       ? 'Status: '.request('status') : null,
                request('level_risiko') ? 'Risiko: '.request('level_risiko') : null,
                request('kategori_id')  ? 'Kategori: '.($kategoriList->firstWhere('id', request('kategori_id'))?->nama_kategori ?? request('kategori_id')) : null,
                request('tanggal_dari') ? 'Dari: '.request('tanggal_dari') : null,
                request('tanggal_sampai') ? 'Sampai: '.request('tanggal_sampai') : null,
            ]);
        @endphp
        @if(count($activeFilters))
        <div class="mt-3 pt-3 border-top d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small">Filter aktif:</span>
            @foreach($activeFilters as $f)
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">{{ $f }}</span>
            @endforeach
            <a href="{{ route('admin.laporan.index') }}" class="text-danger small text-decoration-none ms-2">
                <i class="bi bi-x-circle me-1"></i>Hapus filter
            </a>
        </div>
        @endif
    </div>
</div>

{{-- ── TABLE ──────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>Daftar Laporan</h6>
        <span class="badge bg-secondary">{{ $laporan->total() }} laporan</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Kode</th>
                    <th>Pelapor</th>
                    <th>Kategori & Lokasi</th>
                    <th>Status</th>
                    <th>Risiko</th>
                    <th>Tanggal</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($laporan as $l)
                <tr>
                    <td class="ps-4">
                        <a href="{{ route('admin.laporan.show', $l) }}" class="font-monospace fw-semibold text-primary text-decoration-none">
                            {{ $l->kode_laporan }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-medium text-dark">{{ Str::limit($l->nama_pelapor, 20) }}</div>
                        <div class="text-muted small">{{ $l->kontak_pelapor }}</div>
                    </td>
                    <td>
                        <div class="text-dark">{{ Str::limit($l->kategori?->nama_kategori, 22) ?? '–' }}</div>
                        <div class="text-muted small"><i class="bi bi-geo-alt text-danger me-1"></i>{{ Str::limit($l->lokasi, 30) }}</div>
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
                    <td>
                        @if($l->kategori)
                            @php
                                $risikoClass = match($l->kategori->level_risiko) {
                                    'tinggi' => 'bg-danger',
                                    'sedang' => 'bg-warning text-dark',
                                    default  => 'bg-success',
                                };
                            @endphp
                            <span class="badge {{ $risikoClass }}">{{ ucfirst($l->kategori->level_risiko) }}</span>
                        @else
                            <span class="text-muted">–</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $l->created_at->format('d M Y') }}</td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.laporan.show', $l) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.laporan.edit-status', $l) }}" class="btn btn-sm btn-outline-warning" title="Update Status">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.laporan.edit', $l) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.laporan.destroy', $l) }}" onsubmit="return confirm('Hapus laporan {{ $l->kode_laporan }}? Aksi tidak dapat dibatalkan.')" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                        <p class="mb-2">Tidak ada laporan yang sesuai filter</p>
                        @if(count($activeFilters))
                        <a href="{{ route('admin.laporan.index') }}" class="btn btn-sm btn-light border">
                            <i class="bi bi-x-circle me-1"></i>Hapus Filter
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($laporan->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
        {{ $laporan->links() }}
    </div>
    @endif
</div>

@endsection
