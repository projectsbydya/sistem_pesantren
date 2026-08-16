<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Pelanggaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pelanggaran = $this->route('pelanggaran');
        return $this->user()->can('update', $pelanggaran);
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(Pelanggaran::JENIS_OPTIONS)],
            'kategori' => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(Pelanggaran::STATUS_OPTIONS)],
            'tindak_lanjut' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
