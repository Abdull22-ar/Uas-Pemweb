@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('page-title', 'Tambah Kategori')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Tambah Kategori Sampah</h2>
        <p class="text-muted mb-0">Buat kategori sampah baru dengan level risiko</p>
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
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-plus-circle text-primary me-2"></i>Form Kategori Baru</h5>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('admin.kategori.store') }}" id="formKategori">
                    @csrf

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
                            value="{{ old('nama_kategori') }}"
                            placeholder="Contoh: Sampah Plastik, Sampah B3, Limbah Elektronik..."
                            maxlength="100"
                            required
                        >
                        @error('nama_kategori')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text">Maksimal 100 karakter</div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark" for="deskripsi">Deskripsi</label>
                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}"
                            rows="4"
                            placeholder="Jelaskan jenis sampah dalam kategori ini, cara penanganan, dan potensi bahayanya..."
                        >{{ old('deskripsi') }}</textarea>
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
                            <option value="rendah" {{ old('level_risiko') == 'rendah' ? 'selected' : '' }}>🟢 Rendah – Tidak berbahaya, mudah ditangani</option>
                            <option value="sedang" {{ old('level_risiko') == 'sedang' ? 'selected' : '' }}>🟡 Sedang – Perlu penanganan khusus</option>
                            <option value="tinggi" {{ old('level_risiko') == 'tinggi' ? 'selected' : '' }}>🔴 Tinggi – Berbahaya, prioritas penanganan</option>
                        </select>
                        @error('level_risiko')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror

                        {{-- Risk level preview --}}
                        <div id="riskPreview" class="alert mt-3 mb-0 py-2 px-3 small d-none border"></div>
                    </div>

                    {{-- Status Aktif --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark d-block">Status</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_aktif" id="statusAktif1" value="1"
                                    {{ old('status_aktif', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="statusAktif1">
                                    ✅ Aktif
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_aktif" id="statusAktif0" value="0"
                                    {{ old('status_aktif') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label cursor-pointer" for="statusAktif0">
                                    ⛔ Nonaktif
                                </label>
                            </div>
                        </div>
                        <div class="form-text mt-2">Hanya kategori aktif yang tersedia di form laporan publik</div>
                    </div>

                    <div class="pt-3 border-top mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check-lg me-2"></i>Simpan Kategori
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
        desc: 'Kategori dengan risiko rendah. Sampah jenis ini tidak berbahaya dan dapat ditangani dengan prosedur standar.' 
    },
    sedang: { 
        classNames: ['alert-warning', 'border-warning', 'text-warning-emphasis'],
        desc: 'Kategori dengan risiko sedang. Memerlukan alat pelindung diri (APD) dan penanganan khusus.' 
    },
    tinggi: { 
        classNames: ['alert-danger', 'border-danger', 'text-danger-emphasis'],
        desc: '⚠️ Kategori RISIKO TINGGI. Sampah berbahaya yang memerlukan penanganan prioritas dan tim khusus.' 
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

// Trigger on page load if value selected (after validation fail)
const sel = document.getElementById('level_risiko');
if (sel.value) sel.dispatchEvent(new Event('change'));
</script>

@endsection
