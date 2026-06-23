<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusLaporanRequest extends FormRequest
{
    /**
     * Hanya admin/petugas yang login boleh mengupdate status.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi untuk update status & catatan petugas.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['baru', 'diproses', 'selesai', 'ditolak']),
            ],
            'catatan_petugas' => [
                'nullable',
                'string',
                'max:1000',
                // Jika status 'ditolak', catatan wajib diisi sebagai alasan penolakan
                Rule::requiredIf(fn () => $this->input('status') === 'ditolak'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'          => 'Status laporan wajib dipilih.',
            'status.in'                => 'Status tidak valid. Pilihan: baru, diproses, selesai, ditolak.',
            'catatan_petugas.required' => 'Catatan petugas wajib diisi jika laporan ditolak.',
            'catatan_petugas.max'      => 'Catatan petugas maksimal 1000 karakter.',
        ];
    }
}
