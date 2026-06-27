<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanSampahRequest extends FormRequest
{
    /**
     * Semua user (tamu/pelapor) boleh mengajukan laporan.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk pembuatan laporan baru.
     */
    public function rules(): array
    {
        return [
            'nama_pelapor'   => ['required', 'string', 'min:3', 'max:100'],
            'kontak_pelapor' => ['required', 'string', 'max:20'],
            'kategori_id'    => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('kategori_sampah', 'id')->where('status_aktif', true)
            ],
            'lokasi'         => ['required', 'string', 'min:5', 'max:255'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'deskripsi'      => ['required', 'string'],
            'foto'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'nama_pelapor.required'    => 'Nama pelapor wajib diisi.',
            'nama_pelapor.min'         => 'Nama pelapor minimal 3 karakter.',
            'nama_pelapor.max'         => 'Nama pelapor maksimal 100 karakter.',
            'kontak_pelapor.required'  => 'Kontak pelapor wajib diisi.',
            'kategori_id.required'     => 'Kategori sampah wajib dipilih.',
            'kategori_id.exists'       => 'Kategori sampah yang dipilih tidak ditemukan.',
            'lokasi.required'          => 'Lokasi kejadian wajib diisi.',
            'lokasi.min'               => 'Lokasi minimal 5 karakter.',
            'latitude.between'         => 'Nilai latitude tidak valid (-90 hingga 90).',
            'longitude.between'        => 'Nilai longitude tidak valid (-180 hingga 180).',
            'deskripsi.required'       => 'Deskripsi laporan wajib diisi.',
            'foto.image'               => 'File yang diunggah harus berupa gambar.',
            'foto.mimes'               => 'Format foto harus jpg, jpeg, png, atau webp.',
            'foto.max'                 => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    /**
     * Label nama field untuk pesan error.
     */
    public function attributes(): array
    {
        return [
            'nama_pelapor'   => 'nama pelapor',
            'kontak_pelapor' => 'kontak pelapor',
            'kategori_id'    => 'kategori sampah',
            'lokasi'         => 'lokasi',
            'latitude'       => 'latitude',
            'longitude'      => 'longitude',
            'deskripsi'      => 'deskripsi laporan',
            'foto'           => 'foto',
        ];
    }
}
