<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

final class MovePenempatanKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\PenempatanKamar::class);
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'kamar_tujuan_id' => ['required', 'integer', 'exists:kamar,id'],
            'alasan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
