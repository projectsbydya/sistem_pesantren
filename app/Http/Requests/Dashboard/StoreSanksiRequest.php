<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Sanksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSanksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sanksi::class);
    }

    public function rules(): array
    {
        return [
            'pelanggaran_id' => ['required', 'integer', 'exists:pelanggaran,id'],
            'jenis' => ['required', Rule::in(Sanksi::JENIS_OPTIONS)],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ];
    }
}
