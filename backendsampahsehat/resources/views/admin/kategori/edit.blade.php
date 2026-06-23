@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('page-title', 'Edit Kategori')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Edit Kategori</h2>
        <p class="text-muted mb-0">Perbarui informasi kategori sampah</p>
    </div>
    <div>
        <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-info me-2"></i>Edit: {{ $kategoriSampah->nama_kategori }}</h5>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('admin.kategori.update', $kategoriSampah) }}">
                    @csrf @method('PUT')

                    {{-- Nama Kategori --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark" for="nama_kategori">
                            Nama Kategori <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="nama_kategori"
                            name="nama_kategori"
                            class="form-control {{ $errors->has('nama_kategori') ? 'is-invalid' : '' }}"
                            value="{{ old('nama_kategori', $kategoriSampah->nama_kategori) }}"
                            maxlength="100"
                            required
                        >
                        @error('nama_kategori')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark" for="deskripsi">Deskripsi</label>
                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}"
                            rows="4"
                        >{{ old('deskripsi', $kategoriSampah->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Level Risiko --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark" for="level_risiko">
                            Level Risiko <span class="text-danger">*</span>
                        </label>
                        <select
                            id="level_risiko"
                            name="level_risiko"
                            class="form-select {{ $errors->has('level_risiko') ? 'is-invalid' : '' }}"
                            required
                        >
                            <option value="">-- Pilih Level Risiko --</option>
                            <option value="rendah" {{ old('level_risiko', $kategoriSampah->level_risiko) == 'rendah' ? 'selected' : '' }}>🟢 Rendah</option>
                            <option value="sedang" {{ old('level_risiko', $kategoriSampah->level_risiko) == 'sedang' ? 'selected' : '' }}>🟡 Sedang</option>
                            <option value="tinggi" {{ old('level_risiko', $kategoriSampah->level_risiko) == 'tinggi' ? 'selected' : '' }}>🔴 Tinggi</option>
                        </select>
                        @error('level_risiko')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div id="riskPreview" class="alert mt-3 mb-0 py-2 px-3 small border d-none"></div>
                    </div>

                    {{-- Status Aktif --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark d-block">Status</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_aktif" id="statusAktif1" value="1"
                                    {{ old('status_aktif', $kategoriSampah->status_aktif ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="statusAktif1">
                                    ✅ Aktif
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_aktif" id="statusAktif0" value="0"
                                    {{ old('status_aktif', $kategoriSampah->status_aktif ? '1' : '0') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="statusAktif0">
                                    ⛔ Nonaktif
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Info Kategori --}}
                    <div class="bg-light border rounded-3 p-3 mb-4 d-flex gap-4 flex-wrap align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-hash me-1"></i> ID: <strong class="text-dark">{{ $kategoriSampah->id }}</strong>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-file-earmark-text me-1"></i> Laporan terkait: <strong class="text-dark">{{ $kategoriSampah->laporanSampah()->count() }}</strong>
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> Dibuat: <strong class="text-dark">{{ $kategoriSampah->created_at->format('d M Y') }}</strong>
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.kategori.index') }}" class="btn btn-light border shadow-sm">
                            <i class="bi bi-x-lg me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const riskData = {
    rendah: { 
        classNames: ['alert-success', 'border-success', 'text-success-emphasis'],
        desc: 'Kategori risiko rendah – penanganan prosedur standar.' 
    },
    sedang: { 
        classNames: ['alert-warning', 'border-warning', 'text-warning-emphasis'],
        desc: 'Kategori risiko sedang – perlu APD dan penanganan khusus.' 
    },
    tinggi: { 
        classNames: ['alert-danger', 'border-danger', 'text-danger-emphasis'],
        desc: '⚠️ Kategori RISIKO TINGGI – penanganan prioritas dengan tim khusus.' 
    },
};

document.getElementById('level_risiko').addEventListener('change', function() {
    const preview = document.getElementById('riskPreview');
    const level = this.value;
    
    // Reset classes
    preview.className = 'alert mt-3 mb-0 py-2 px-3 small border d-none';
    
    if (level && riskData[level]) {
        const d = riskData[level];
        preview.classList.remove('d-none');
        d.classNames.forEach(c => preview.classList.add(c));
        preview.innerHTML = '<i class="bi bi-info-circle-fill me-2"></i> ' + d.desc;
    }
});
document.getElementById('level_risiko').dispatchEvent(new Event('change'));
</script>

@endsection
