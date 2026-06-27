<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use App\Models\LaporanSampah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LaporanSampahController extends Controller
{
    // =========================================================================
    // HELPER – Format Response JSON Konsisten
    // =========================================================================

    /**
     * Buat response JSON sukses dengan struktur yang konsisten.
     *
     * @param  mixed   $data
     * @param  string  $message
     * @param  int     $statusCode
     * @param  array   $meta       data tambahan (pagination, dsb.)
     */
    private function successResponse(
        mixed  $data,
        string $message = 'Berhasil.',
        int    $statusCode = 200,
        array  $meta = []
    ): JsonResponse {
        $body = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (! empty($meta)) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $statusCode);
    }

    /**
     * Buat response JSON error dengan struktur yang konsisten.
     *
     * @param  string  $message
     * @param  int     $statusCode
     * @param  array   $errors      detail error field (dari validasi)
     */
    private function errorResponse(
        string $message,
        int    $statusCode = 400,
        array  $errors = []
    ): JsonResponse {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $statusCode);
    }

    /**
     * Format data laporan menjadi array JSON yang bersih.
     */
    private function formatLaporan(LaporanSampah $laporan, bool $withKategori = true): array
    {
        $data = [
            'kode_laporan'    => $laporan->kode_laporan,
            'nama_pelapor'    => $laporan->nama_pelapor,
            'kontak_pelapor'  => $laporan->kontak_pelapor,
            'lokasi'          => $laporan->lokasi,
            'koordinat'       => $laporan->koordinat,       // accessor: {lat, lng} atau null
            'deskripsi'       => $laporan->deskripsi,
            'foto_url'        => $laporan->foto_url,         // accessor: URL lengkap atau null
            'status'          => $laporan->status,
            'label_status'    => $laporan->label_status,    // accessor: emoji + teks
            'catatan_petugas' => $laporan->catatan_petugas,
            'dibuat_pada'     => $laporan->created_at?->toIso8601String(),
            'diperbarui_pada' => $laporan->updated_at?->toIso8601String(),
        ];

        if ($withKategori && $laporan->relationLoaded('kategori') && $laporan->kategori) {
            $data['kategori'] = [
                'id'            => $laporan->kategori->id,
                'nama_kategori' => $laporan->kategori->nama_kategori,
                'level_risiko'  => $laporan->kategori->level_risiko,
                'label_risiko'  => $laporan->kategori->label_risiko,
            ];
        }

        if ($laporan->relationLoaded('petugas') && $laporan->petugas) {
            $data['petugas'] = [
                'nama'         => $laporan->petugas->name,
                'kontak'       => $laporan->petugas->kontak,
                'spesialisasi' => $laporan->petugas->lokasi,
            ];
        }

        return $data;
    }

    // =========================================================================
    // GET /api/laporan-publik
    // Mengambil semua laporan (untuk daftar laporan publik terbatas)
    // =========================================================================

    public function laporanPublik(Request $request): JsonResponse
    {
        $query = LaporanSampah::with('kategori', 'petugas')->latest();

        $perPage = max(1, min((int) $request->input('per_page', 15), 100));
        $result  = $query->paginate($perPage);

        $data = collect($result->items())
            ->map(function ($l) {
                $item = [
                    'kode_laporan'  => $l->kode_laporan,
                    'kategori'      => $l->kategori ? $l->kategori->nama_kategori : '-',
                    'status'        => $l->status,
                    'label_status'  => $l->label_status,
                    'lokasi'        => $l->lokasi,
                    'koordinat'     => $l->koordinat,
                    'dibuat_pada'   => $l->created_at?->toIso8601String(),
                ];
                if ($l->relationLoaded('petugas') && $l->petugas) {
                    $item['petugas'] = $l->petugas->name;
                }
                return $item;
            })
            ->values();

        return $this->successResponse(
            $data,
            "Daftar laporan publik berhasil diambil.",
            200,
            [
                'total'          => $result->total(),
                'per_page'       => $result->perPage(),
                'current_page'   => $result->currentPage(),
                'last_page'      => $result->lastPage(),
                'has_more_pages' => $result->hasMorePages(),
            ]
        );
    }

    // =========================================================================
    // POST /api/laporan-sampah
    // Menyimpan laporan baru dari frontend (pelapor publik)
    // =========================================================================

    /**
     * Terima dan validasi laporan baru dari frontend PHP Native.
     *
     * Request Body (form-data atau JSON):
     *   - nama_pelapor   : string, wajib, min 3 karakter
     *   - kontak_pelapor : string, wajib, format nomor telepon
     *   - kategori_id    : integer, wajib, harus ada di tabel kategori_sampah & aktif
     *   - lokasi         : string, wajib, min 5 karakter
     *   - latitude       : float, opsional, -90 s/d 90
     *   - longitude      : float, opsional, -180 s/d 180
     *   - deskripsi      : string, wajib
     *   - foto           : file gambar, opsional, maks 2MB (jpg/jpeg/png/webp)
     *
     * Response 201 Created:
     * {
     *   "success": true,
     *   "message": "Laporan berhasil dikirim.",
     *   "data": { kode_laporan, nama_pelapor, status, ... }
     * }
     *
     * Response 422 Unprocessable Entity (validasi gagal):
     * {
     *   "success": false,
     *   "message": "Data tidak valid.",
     *   "errors": { "field": ["pesan error"], ... }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        // ── Validasi Input ─────────────────────────────────────────────────
        $validator = Validator::make($request->all(), [
            'nama_pelapor'   => ['required', 'string', 'min:3', 'max:100'],
            'kontak_pelapor' => ['required', 'string', 'max:20'],
            'kategori_id'    => [
                'required',
                'integer',
                Rule::exists('kategori_sampah', 'id')->where('status_aktif', true),
            ],
            'lokasi'         => ['required', 'string', 'min:5', 'max:255'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'deskripsi'      => ['required', 'string'],
            'foto'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama_pelapor.required'    => 'Nama pelapor wajib diisi.',
            'nama_pelapor.min'         => 'Nama pelapor minimal 3 karakter.',
            'kontak_pelapor.required'  => 'Nomor kontak wajib diisi.',
            'kategori_id.required'     => 'Kategori sampah wajib dipilih.',
            'kategori_id.exists'       => 'Kategori tidak ditemukan atau tidak aktif.',
            'lokasi.required'          => 'Lokasi kejadian wajib diisi.',
            'lokasi.min'               => 'Lokasi minimal 5 karakter.',
            'latitude.between'         => 'Nilai latitude tidak valid (-90 s/d 90).',
            'longitude.between'        => 'Nilai longitude tidak valid (-180 s/d 180).',
            'deskripsi.required'       => 'Deskripsi laporan wajib diisi.',
            'foto.image'               => 'File harus berupa gambar.',
            'foto.mimes'               => 'Format foto: jpg, jpeg, png, atau webp.',
            'foto.max'                 => 'Ukuran foto maksimal 2 MB.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Data yang dikirim tidak valid. Periksa kembali isian Anda.',
                422,
                $validator->errors()->toArray()
            );
        }

        // ── Proses Upload Foto ─────────────────────────────────────────────
        $data = $validator->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('laporan/foto', 'public');
        }

        // ── Simpan ke DB (kode_laporan di-generate otomatis di Model::boot) ─
        $laporan = LaporanSampah::create($data);
        $laporan->load('kategori');

        return $this->successResponse(
            $this->formatLaporan($laporan),
            "Laporan berhasil dikirim! Kode laporan Anda adalah {$laporan->kode_laporan}. Simpan kode ini untuk memantau status laporan.",
            201
        );
    }

    // =========================================================================
    // GET /api/laporan-sampah/{kode_laporan}
    // Tracking laporan berdasarkan kode unik (diakses oleh pelapor)
    // =========================================================================

    /**
     * Cari dan tampilkan status laporan berdasarkan kode_laporan.
     *
     * Path Parameter:
     *   - kode_laporan : string, contoh: SPH-001
     *
     * Response 200 OK:
     * {
     *   "success": true,
     *   "data": { kode_laporan, status, label_status, kategori, ... }
     * }
     *
     * Response 404 Not Found:
     * {
     *   "success": false,
     *   "message": "Laporan tidak ditemukan."
     * }
     */
    public function show(string $kodeLaporan): JsonResponse
    {
        $laporan = LaporanSampah::with('kategori', 'petugas')
            ->where('kode_laporan', strtoupper(trim($kodeLaporan)))
            ->first();

        if (! $laporan) {
            return $this->errorResponse(
                "Laporan dengan kode '{$kodeLaporan}' tidak ditemukan. Pastikan kode laporan yang Anda masukkan benar.",
                404
            );
        }

        return $this->successResponse(
            $this->formatLaporan($laporan),
            'Data laporan ditemukan.'
        );
    }


}
