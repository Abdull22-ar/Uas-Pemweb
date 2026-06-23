@extends('layouts.admin')

@section('title', 'Edit Laporan')
@section('page-title', 'Edit Laporan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Edit Laporan</h2>
        <p class="text-muted mb-0">
            <span class="font-monospace text-primary fw-bold">{{ $laporanSampah->kode_laporan }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.laporan.show', $laporanSampah) }}" class="btn btn-outline-info shadow-sm">
            <i class="bi bi-eye me-2"></i>Lihat Detail
        </a>
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-info me-2"></i>Form Edit Data Laporan</h5>
                <span class="badge bg-secondary">{{ $laporanSampah->kode_laporan }}</span>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('admin.laporan.update', $laporanSampah) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    {{-- Info row --}}
                    <div class="bg-light border rounded-3 p-3 mb-4 d-flex gap-4 flex-wrap align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> Dibuat: 
                            <strong class="text-dark">{{ $laporanSampah->created_at->format('d M Y H:i') }}</strong>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-arrow-repeat me-1"></i> Status saat ini:
                            @php 
                                $sc = match($laporanSampah->status) { 
                                    'baru'=>'bg-info text-dark',
                                    'diproses'=>'bg-warning text-dark',
                                    'selesai'=>'bg-success',
                                    'ditolak'=>'bg-danger',
                                    default=>'bg-secondary' 
                                }; 
                            @endphp
                            <span class="badge rounded-pill {{ $sc }} ms-1">{{ ucfirst($laporanSampah->status) }}</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        {{-- Nama Pelapor --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" for="nama_pelapor">
                                Nama Pelapor <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="nama_pelapor" name="nama_pelapor"
                                class="form-control {{ $errors->has('nama_pelapor') ? 'is-invalid' : '' }}"
                                value="{{ old('nama_pelapor', $laporanSampah->nama_pelapor) }}"
                                maxlength="100" required>
                            @error('nama_pelapor')
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kontak --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" for="kontak_pelapor">
                                Kontak <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="kontak_pelapor" name="kontak_pelapor"
                                class="form-control {{ $errors->has('kontak_pelapor') ? 'is-invalid' : '' }}"
                                value="{{ old('kontak_pelapor', $laporanSampah->kontak_pelapor) }}"
                                maxlength="255" required>
                            @error('kontak_pelapor')
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Kategori --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="kategori_id">
                            Kategori Sampah <span class="text-danger">*</span>
                        </label>
                        <select id="kategori_id" name="kategori_id"
                            class="form-select {{ $errors->has('kategori_id') ? 'is-invalid' : '' }}"
                            required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_id', $laporanSampah->kategori_id) == $kat->id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }} ({{ ucfirst($kat->level_risiko) }})
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Lokasi --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="lokasi">
                            Lokasi <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="lokasi" name="lokasi"
                            class="form-control {{ $errors->has('lokasi') ? 'is-invalid' : '' }}"
                            value="{{ old('lokasi', $laporanSampah->lokasi) }}"
                            minlength="5" maxlength="255" required>
                        @error('lokasi')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" for="latitude">Latitude</label>
                            <input type="number" id="latitude" name="latitude"
                                class="form-control {{ $errors->has('latitude') ? 'is-invalid' : '' }}"
                                value="{{ old('latitude', $laporanSampah->latitude) }}"
                                step="any" min="-90" max="90">
                            @error('latitude') 
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" for="longitude">Longitude</label>
                            <input type="number" id="longitude" name="longitude"
                                class="form-control {{ $errors->has('longitude') ? 'is-invalid' : '' }}"
                                value="{{ old('longitude', $laporanSampah->longitude) }}"
                                step="any" min="-180" max="180">
                            @error('longitude') 
                                <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div> 
                            @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" for="deskripsi">
                            Deskripsi <span class="text-danger">*</span>
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="5"
                            class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}"
                            minlength="5" required>{{ old('deskripsi', $laporanSampah->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Foto --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Foto</label>

                        @if($laporanSampah->foto_url)
                        <div class="d-flex align-items-center gap-3 p-3 bg-light border rounded-3 mb-3">
                            <img src="{{ $laporanSampah->foto_url }}" alt="Foto saat ini" class="rounded shadow-sm" style="width: 80px; height: 60px; object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark small mb-1">Foto saat ini</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hapus_foto" value="1" id="hapusFoto">
                                    <label class="form-check-label text-danger small cursor-pointer" for="hapusFoto">
                                        Hapus foto ini
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif

                        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                            class="form-control {{ $errors->has('foto') ? 'is-invalid' : '' }}">
                        @error('foto')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text">JPG, PNG, WebP – maks 2 MB. Biarkan kosong jika tidak ingin mengganti foto.</div>
                    </div>

                    <div class="pt-3 border-top mt-4 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.laporan.edit-status', $laporanSampah) }}" class="btn btn-warning shadow-sm">
                            <i class="bi bi-arrow-repeat me-2"></i>Update Status
                        </a>
                        <a href="{{ route('admin.laporan.index') }}" class="btn btn-light border shadow-sm">
                            <i class="bi bi-x-lg me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
