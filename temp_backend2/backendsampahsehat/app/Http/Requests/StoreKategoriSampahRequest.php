<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriSampahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100', 'unique:kategori_sampah,nama_kategori'],
            'deskripsi'     => ['nullable', 'string', 'max:500'],
            'level_risiko'  => ['required', 'in:rendah,sedang,tinggi'],
            'status_aktif'  => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min'      => 'Nama kategori minimal 3 karakter.',
            'nama_kategori.max'      => 'Nama kategori maksimal 100 karakter.',
            'nama_kategori.unique'   => 'Nama kategori sudah terdaftar.',
            'level_risiko.required'  => 'Level risiko wajib dipilih.',
            'level_risiko.in'        => 'Level risiko harus: rendah, sedang, atau tinggi.',
        ];
    }
}
