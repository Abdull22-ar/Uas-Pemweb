@extends('layouts.admin')

@section('title', 'Update Status Laporan')
@section('page-title', 'Update Status Laporan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Update Status Laporan</h2>
        <p class="text-muted mb-0">
            <span class="font-monospace text-primary fw-bold">{{ $laporanSampah->kode_laporan }}</span> – {{ $laporanSampah->nama_pelapor }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.laporan.show', $laporanSampah) }}" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-eye me-2"></i>Detail
        </a>
        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-arrow-repeat text-warning me-2"></i>Form Update Status</h5>
            </div>
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('admin.laporan.update-status', $laporanSampah) }}" id="updateStatusForm">
                    @csrf @method('PATCH')

                    {{-- Status saat ini --}}
                    <div class="bg-light border rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small fw-bold text-uppercase tracking-wider">Status Saat Ini</div>
                        @php
                            $sc = match($laporanSampah->status) {
                                'baru'     => 'bg-info text-dark',
                                'diproses' => 'bg-warning text-dark',
                                'selesai'  => 'bg-success',
                                'ditolak'  => 'bg-danger',
                                default    => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge rounded-pill {{ $sc }} px-3 py-2 fs-6">
                            {{ $laporanSampah->label_status }}
                        </span>
                    </div>

                    {{-- Pilihan Status --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Status Baru <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-3" id="statusOptions">
                            @php
                                $statuses = [
                                    'baru'     => ['icon'=>'bi-inbox text-info', 'label'=>'Baru', 'desc'=>'Laporan baru masuk, belum ditangani'],
                                    'diproses' => ['icon'=>'bi-gear text-warning', 'label'=>'Sedang Diproses', 'desc'=>'Laporan sedang dalam penanganan petugas'],
                                    'selesai'  => ['icon'=>'bi-check-circle text-success', 'label'=>'Selesai', 'desc'=>'Laporan telah selesai ditangani'],
                                    'ditolak'  => ['icon'=>'bi-x-circle text-danger', 'label'=>'Ditolak', 'desc'=>'Laporan tidak dapat diproses (wajib isi catatan)'],
                                ];
                            @endphp

                            @foreach($statuses as $val => $cfg)
                            <label class="form-check p-3 border rounded-3 m-0 status-option cursor-pointer transition-all" style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <input class="form-check-input mt-0 me-3" type="radio" name="status" value="{{ $val }}" 
                                        {{ old('status', $laporanSampah->status) == $val ? 'checked' : '' }}
                                        onchange="handleStatusChange('{{ $val }}')" style="width: 1.25em; height: 1.25em;">
                                    <div>
                                        <div class="fw-bold text-dark mb-1"><i class="bi {{ $cfg['icon'] }} me-2"></i>{{ $cfg['label'] }}</div>
                                        <div class="text-muted small">{{ $cfg['desc'] }}</div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('status')
                            <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Catatan Petugas --}}
                    <div class="mb-4" id="catatanGroup">
                        <label class="form-label fw-bold text-dark" for="catatan_petugas">
                            Catatan Petugas
                            <span id="catatanRequired" class="text-danger" style="display:none;">*</span>
                            <span id="catatanOptional" class="text-muted fw-normal small ms-1">(opsional)</span>
                        </label>
                        <textarea id="catatan_petugas" name="catatan_petugas" rows="4"
                            class="form-control {{ $errors->has('catatan_petugas') ? 'is-invalid' : '' }}"
                            placeholder="Tulis catatan, tindakan yang dilakukan, atau alasan penolakan...">{{ old('catatan_petugas', $laporanSampah->catatan_petugas) }}</textarea>
                        @error('catatan_petugas')
                            <div class="invalid-feedback"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-2" id="catatanHint">Catatan ini akan ditampilkan kepada pelapor.</div>
                    </div>

                    {{-- Petugas Lapangan (khusus rendah / sedang / tinggi) --}}
                    @php $levelRisiko = $laporanSampah->kategori?->level_risiko; @endphp
                    @if(in_array($levelRisiko, ['rendah', 'sedang', 'tinggi']))
                    <div class="mb-4" id="petugasSection" style="display:none;">
                        <label class="form-label fw-bold text-dark">Petugas Penanganan <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-3">Pilih petugas yang akan menangani laporan ini beserta bidangnya:</p>
                        <div class="d-flex flex-column gap-2" id="petugasOptions">
                            @php
                                $petugasList = match($levelRisiko) {
                                    'rendah' => $petugasRendah,
                                    'sedang' => $petugasSedang,
                                    'tinggi' => $petugasTinggi,
                                };
                            @endphp
                            @foreach($petugasList as $p)
                            @php
                                $inisial = strtoupper(substr($p->name, 0, 1));
                                $warnaBg = match($loop->index % 4) { 0 => '#2e8b57', 1 => '#0d6efd', 2 => '#6f42c1', 3 => '#e67e22' };
                            @endphp
                            <label class="form-check p-3 border rounded-3 m-0 petugas-option cursor-pointer transition-all" style="cursor:pointer;border-left:4px solid {{ $warnaBg }};">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 flex-shrink-0" type="radio" name="petugas_id" value="{{ $p->id }}"
                                        {{ old('petugas_id', $laporanSampah->petugas_id) == $p->id ? 'checked' : '' }}
                                        style="width:1.25em;height:1.25em;">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width:40px;height:40px;background:{{ $warnaBg }};font-size:16px;">
                                            {{ $inisial }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:14px;">{{ $p->name }}</div>
                                            <div class="d-flex gap-3 small text-muted">
                                                <span><i class="bi bi-tools me-1"></i>{{ $p->lokasi ?? $levelRisiko }}</span>
                                                <span><i class="bi bi-telephone me-1"></i>{{ $p->kontak }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('petugas_id')
                            <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-2">Pilih petugas yang akan ditugaskan menangani laporan ini.</div>
                    </div>
                    @endif

                    <div class="pt-3 border-top mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm">
                            <i class="bi bi-check-lg me-2"></i>Simpan Perubahan Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Ringkasan Detail + Map --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 85px;">
            <div class="card-header bg-white p-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-info me-2"></i>Ringkasan Laporan</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush rounded-bottom-4">
                    <li class="list-group-item p-3 d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small text-uppercase">Nama Pelapor</div>
                            <div class="text-dark">{{ $laporanSampah->nama_pelapor }}</div>
                        </div>
                    </li>
                    <li class="list-group-item p-3 d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small text-uppercase">Kontak</div>
                            <div class="text-dark">{{ $laporanSampah->kontak_pelapor }}</div>
                        </div>
                    </li>
                    <li class="list-group-item p-3 d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small text-uppercase">Kategori</div>
                            <div class="text-dark">{{ $laporanSampah->kategori?->nama_kategori ?? '–' }}</div>
                        </div>
                    </li>
                    <li class="list-group-item p-3 d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small text-uppercase">Lokasi</div>
                            <div class="text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $laporanSampah->lokasi }}</div>
                        </div>
                    </li>
                    <li class="list-group-item p-3 d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small text-uppercase">Petugas</div>
                            @if($laporanSampah->relationLoaded('petugas') && $laporanSampah->petugas)
                                <div class="text-dark">{{ $laporanSampah->petugas->name }}</div>
                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $laporanSampah->petugas->kontak ?? '-' }}</div>
                            @else
                                <div class="text-muted">–</div>
                            @endif
                        </div>
                    </li>
                    <li class="list-group-item p-3">
                        <div class="ms-2">
                            <div class="fw-bold text-muted small text-uppercase mb-1">Deskripsi Singkat</div>
                            <div class="text-dark small">{{ Str::limit($laporanSampah->deskripsi, 100) }}</div>
                        </div>
                    </li>
                    @if($laporanSampah->latitude && $laporanSampah->longitude)
                    <li class="list-group-item p-0">
                        <div id="editStatusMap" style="height: 200px; width: 100%; border-radius: 0 0 var(--bs-card-inner-border-radius, 0.375rem) var(--bs-card-inner-border-radius, 0.375rem);"></div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@if($laporanSampah->latitude && $laporanSampah->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('editStatusMap', { zoomControl: false }).setView([{{ $laporanSampah->latitude }}, {{ $laporanSampah->longitude }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    L.marker([{{ $laporanSampah->latitude }}, {{ $laporanSampah->longitude }}]).addTo(map);
});
</script>
@endif

<script>
function handleStatusChange(val) {
    const req = document.getElementById('catatanRequired');
    const opt = document.getElementById('catatanOptional');
    const hint = document.getElementById('catatanHint');
    const textarea = document.getElementById('catatan_petugas');
    const petugasSection = document.getElementById('petugasSection');
    const petugasRadios = document.querySelectorAll('input[name="petugas_id"]');

    if (val === 'ditolak') {
        req.style.display = 'inline';
        opt.style.display = 'none';
        textarea.required = true;
        textarea.placeholder = 'Wajib: tulis alasan penolakan laporan...';
        hint.classList.add('text-danger', 'fw-bold');
        hint.classList.remove('text-muted');
        hint.textContent = 'Catatan WAJIB diisi jika status Ditolak.';
    } else {
        req.style.display = 'none';
        opt.style.display = 'inline';
        textarea.required = false;
        textarea.placeholder = 'Tulis catatan, tindakan yang dilakukan, atau alasan penolakan...';
        hint.classList.remove('text-danger', 'fw-bold');
        hint.classList.add('text-muted');
        hint.textContent = 'Catatan ini akan ditampilkan kepada pelapor.';
    }

    // Tampilkan/sembunyikan petugas section
    if (petugasSection) {
        if (val === 'diproses') {
            petugasSection.style.display = 'block';
            if (petugasRadios.length) petugasRadios.forEach(r => r.required = true);
        } else {
            petugasSection.style.display = 'none';
            if (petugasRadios.length) petugasRadios.forEach(r => r.required = false);
        }
    }

    document.querySelectorAll('.status-option').forEach(el => {
        const radio = el.querySelector('input[type="radio"]');
        if(radio.checked) {
            el.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            el.classList.remove('border-muted');
        } else {
            el.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const selected = document.querySelector('input[name="status"]:checked');
    if (selected) handleStatusChange(selected.value);
});
</script>

@endsection
