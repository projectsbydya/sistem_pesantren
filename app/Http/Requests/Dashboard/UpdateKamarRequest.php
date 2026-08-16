<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Kamar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kamar = $this->route('kamar');
        return $this->user()->can('update', $kamar);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', Rule::in(Kamar::STATUS_OPTIONS)],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'fasilitas' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
