<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Pelanggaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Pelanggaran::class);
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'jenis' => ['required', Rule::in(Pelanggaran::JENIS_OPTIONS)],
            'kategori' => ['required', 'string', 'max:100'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ];
    }
}
