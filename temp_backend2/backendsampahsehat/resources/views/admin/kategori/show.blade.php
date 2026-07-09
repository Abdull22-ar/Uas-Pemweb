@extends('layouts.admin')

@section('title', 'Detail Kategori')
@section('page-title', 'Detail Kategori')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ $kategoriSampah->nama_kategori }}</h2>
        <p class="text-muted mb-0">Detail dan laporan terkait kategori ini</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.kategori.edit', $kategoriSampah) }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        {{-- Info Card --}}
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-tag-fill text-primary me-2"></i>Informasi Kategori</h5>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-sm-6 border-bottom border-end p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1">ID Kategori</div>
                        <div class="fw-bold text-dark fs-5">#{{ $kategoriSampah->id }}</div>
                    </div>
                    <div class="col-sm-6 border-bottom p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Nama Kategori</div>
                        <div class="fw-bold text-dark fs-5">{{ $kategoriSampah->nama_kategori }}</div>
                    </div>
                    <div class="col-sm-6 border-bottom border-end p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">Level Risiko</div>
                        @php
                            $risikoClass = match($kategoriSampah->level_risiko) {
                                'tinggi' => 'bg-danger',
                                'sedang' => 'bg-warning text-dark',
                                default  => 'bg-success',
                            };
                        @endphp
                        <span class="badge rounded-pill {{ $risikoClass }} px-3 py-2">
                            {{ $kategoriSampah->label_risiko }}
                        </span>
                    </div>
                    <div class="col-sm-6 border-bottom p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">Status</div>
                        <span class="badge rounded-pill {{ $kategoriSampah->status_aktif ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                            {{ $kategoriSampah->status_aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="col-12 border-bottom p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-2">Deskripsi</div>
                        <p class="text-dark mb-0" style="line-height: 1.6;">{{ $kategoriSampah->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                    </div>
                    <div class="col-sm-6 border-end p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Dibuat Pada</div>
                        <div class="text-dark">{{ $kategoriSampah->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-sm-6 p-4">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Terakhir Diperbarui</div>
                        <div class="text-dark">{{ $kategoriSampah->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats & Actions --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white overflow-hidden position-relative">
            <div class="position-absolute" style="right: -20px; top: -20px; opacity: 0.1;">
                <i class="bi bi-file-earmark-text" style="font-size: 8rem;"></i>
            </div>
            <div class="card-body p-4 position-relative">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-25 rounded p-2 me-3">
                        <i class="bi bi-file-earmark-text fs-3"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-white-50 text-uppercase fw-bold small">Total Laporan</h6>
                    </div>
                </div>
                <h2 class="display-4 fw-bold mb-0">{{ $laporan->total() }}</h2>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h6 class="fw-bold text-muted text-uppercase small mb-0">Aksi Cepat</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.kategori.edit', $kategoriSampah) }}" class="btn btn-outline-primary text-start">
                        <i class="bi bi-pencil me-2"></i>Edit Kategori
                    </a>
                    
                    <form method="POST" action="{{ route('admin.kategori.toggle-status', $kategoriSampah) }}" class="d-grid">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-toggle-{{ $kategoriSampah->status_aktif ? 'on text-success' : 'off text-muted' }} me-2"></i>
                            {{ $kategoriSampah->status_aktif ? 'Nonaktifkan Kategori' : 'Aktifkan Kategori' }}
                        </button>
                    </form>

                    @if($laporan->total() === 0)
                    <form method="POST" action="{{ route('admin.kategori.destroy', $kategoriSampah) }}" onsubmit="return confirm('Yakin hapus kategori ini?')" class="d-grid mt-2 border-top pt-3">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger text-start">
                            <i class="bi bi-trash me-2"></i>Hapus Kategori
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── LAPORAN TERKAIT ─────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text text-info me-2"></i>Laporan Terkait Kategori Ini</h5>
        <span class="badge bg-secondary">{{ $laporan->total() }} laporan</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Kode</th>
                    <th>Pelapor</th>
                    <th>Lokasi</th>
                    <th>Status</th>
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
                        <div class="fw-medium text-dark">{{ $l->nama_pelapor }}</div>
                    </td>
                    <td>
                        <div class="text-dark small"><i class="bi bi-geo-alt text-danger me-1"></i>{{ Str::limit($l->lokasi, 40) }}</div>
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
                    <td class="text-muted small">{{ $l->created_at->format('d M Y') }}</td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.laporan.show', $l) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.laporan.edit-status', $l) }}" class="btn btn-sm btn-outline-warning" title="Update Status">
                                <i class="bi bi-arrow-repeat"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                        <p class="mb-0">Belum ada laporan untuk kategori ini</p>
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
