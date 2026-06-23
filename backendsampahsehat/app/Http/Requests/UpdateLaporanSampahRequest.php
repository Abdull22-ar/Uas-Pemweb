<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaporanSampahRequest extends FormRequest
{
    /**
     * Hanya admin/petugas yang login boleh mengupdate laporan.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi untuk update laporan (semua kolom opsional kecuali diperlukan).
     */
    public function rules(): array
    {
        return [
            'nama_pelapor'   => ['sometimes', 'required', 'string', 'min:3', 'max:100'],
            'kontak_pelapor' => ['sometimes', 'required', 'string', 'max:255'],
            'kategori_id'    => ['sometimes', 'required', 'integer', 'exists:kategori_sampah,id'],
            'lokasi'         => ['sometimes', 'required', 'string', 'min:5', 'max:255'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'deskripsi'      => ['sometimes', 'required', 'string', 'min:5'],
            'foto'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_foto'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_pelapor.required'    => 'Nama pelapor wajib diisi.',
            'nama_pelapor.min'         => 'Nama pelapor minimal 3 karakter.',
            'kontak_pelapor.required'  => 'Kontak pelapor wajib diisi.',
            'kategori_id.required'     => 'Kategori sampah wajib dipilih.',
            'kategori_id.exists'       => 'Kategori sampah tidak ditemukan.',
            'lokasi.required'          => 'Lokasi wajib diisi.',
            'deskripsi.required'       => 'Deskripsi laporan wajib diisi.',
            'deskripsi.min'            => 'Deskripsi minimal 5 karakter.',
            'foto.image'               => 'File harus berupa gambar.',
            'foto.mimes'               => 'Format foto harus jpg, jpeg, png, atau webp.',
            'foto.max'                 => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
