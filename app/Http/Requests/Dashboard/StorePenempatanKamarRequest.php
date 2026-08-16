<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\PenempatanKamar;
use Illuminate\Foundation\Http\FormRequest;

final class StorePenempatanKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PenempatanKamar::class);
    }

    public function rules(): array
    {
        return [
            'kamar_id' => ['required', 'integer', 'exists:kamar,id'],
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'tanggal_masuk' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
