@extends('layouts.admin')

@section('title', 'Kategori Sampah')
@section('page-title', 'Kategori Sampah')

@section('content')

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h2 class="fw-bold mb-1">Kategori Sampah</h2>
        <p class="text-muted mb-0">Kelola kategori dan level risiko sampah</p>
    </div>
    @if(auth()->user()->role === 'admin')
    <div>
        <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Tambah Kategori
        </a>
    </div>
    @endif
</div>

{{-- ── FILTER BAR ────────────────────────────────────────────── --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.kategori.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold text-uppercase">Cari</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nama atau deskripsi..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Level Risiko</label>
                <select name="level_risiko" class="form-select">
                    <option value="">Semua Level</option>
                    <option value="rendah"  {{ request('level_risiko') == 'rendah'  ? 'selected' : '' }}>Rendah</option>
                    <option value="sedang"  {{ request('level_risiko') == 'sedang'  ? 'selected' : '' }}>Sedang</option>
                    <option value="tinggi"  {{ request('level_risiko') == 'tinggi'  ? 'selected' : '' }}>Tinggi</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                <select name="status_aktif" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel me-2"></i>Filter
                </button>
                <a href="{{ route('admin.kategori.index') }}" class="btn btn-light border">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>

        @php
            $activeFilters = array_filter([
                request('search')       ? 'Cari: "'.request('search').'"' : null,
                request('level_risiko') ? 'Risiko: '.request('level_risiko') : null,
                request('status_aktif') !== null ? 'Status: '.(request('status_aktif') ? 'Aktif' : 'Nonaktif') : null,
            ]);
        @endphp
        @if(count($activeFilters))
        <div class="mt-3 pt-3 border-top d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small">Filter aktif:</span>
            @foreach($activeFilters as $f)
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">{{ $f }}</span>
            @endforeach
            <a href="{{ route('admin.kategori.index') }}" class="text-danger small text-decoration-none ms-2">
                <i class="bi bi-x-circle me-1"></i>Hapus filter
            </a>
        </div>
        @endif
    </div>
</div>

{{-- ── TABLE ─────────────────────────────────────────────────── --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0"><i class="bi bi-tags text-primary me-2"></i>Daftar Kategori</h6>
        <span class="badge bg-secondary">{{ $kategori->total() }} kategori</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Level Risiko</th>
                    <th>Status</th>
                    <th>Jml. Laporan</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($kategori as $kat)
                <tr>
                    <td class="text-muted small ps-4">
                        {{ $loop->iteration + ($kategori->currentPage() - 1) * $kategori->perPage() }}
                    </td>
                    <td>
                        <div class="fw-medium text-dark">{{ $kat->nama_kategori }}</div>
                    </td>
                    <td>
                        <div class="text-muted small" style="max-width:260px;">
                            {{ Str::limit($kat->deskripsi, 70) ?: '–' }}
                        </div>
                    </td>
                    <td>
                        @php
                            $risikoClass = match($kat->level_risiko) {
                                'tinggi' => 'bg-danger',
                                'sedang' => 'bg-warning text-dark',
                                default  => 'bg-success',
                            };
                        @endphp
                        <span class="badge rounded-pill {{ $risikoClass }}">{{ ucfirst($kat->level_risiko) }}</span>
                    </td>
                    <td>
                        @if(auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('admin.kategori.toggle-status', $kat) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm p-0 border-0" title="{{ $kat->status_aktif ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                        @endif
                                <span class="badge rounded-pill {{ $kat->status_aktif ? 'bg-success' : 'bg-secondary opacity-75' }}">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                                    {{ $kat->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                        @if(auth()->user()->role === 'admin')
                            </button>
                        </form>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold text-dark">{{ $kat->laporan_sampah_count }}</span>
                        <span class="text-muted small">laporan</span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.kategori.show', $kat) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.kategori.edit', $kat) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.kategori.destroy', $kat) }}" onsubmit="return confirm('Yakin ingin menghapus kategori \'{{ addslashes($kat->nama_kategori) }}\'? Aksi ini tidak dapat dibatalkan.')" class="d-inline">
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
                        <i class="bi bi-tags fs-1 d-block mb-3 opacity-50"></i>
                        <p class="mb-2">Belum ada kategori sampah</p>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.kategori.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($kategori->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
        {{ $kategori->links() }}
    </div>
    @endif
</div>

@endsection
