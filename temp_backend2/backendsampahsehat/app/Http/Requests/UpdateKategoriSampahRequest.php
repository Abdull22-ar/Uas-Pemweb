<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriSampahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        // Abaikan unique constraint untuk record yang sedang diedit
        // Nama parameter sesuai ->parameters(['kategori' => 'kategoriSampah']) di web.php
        $kategori = $this->route('kategoriSampah');
        $id = $kategori instanceof \App\Models\KategoriSampah ? $kategori->id : $kategori;

        return [
            'nama_kategori' => [
                'sometimes', 'required', 'string', 'min:3', 'max:100',
                Rule::unique('kategori_sampah', 'nama_kategori')->ignore($id),
            ],
            'deskripsi'    => ['nullable', 'string', 'max:500'],
            'level_risiko' => ['sometimes', 'required', 'in:rendah,sedang,tinggi'],
            'status_aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah digunakan oleh kategori lain.',
            'level_risiko.required'  => 'Level risiko wajib dipilih.',
            'level_risiko.in'        => 'Level risiko harus: rendah, sedang, atau tinggi.',
        ];
    }
}
