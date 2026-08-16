<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Sanksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSanksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sanksi = $this->route('sanksi');
        return $this->user()->can('update', $sanksi);
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(Sanksi::JENIS_OPTIONS)],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['nullable', Rule::in(Sanksi::STATUS_OPTIONS)],
            'hasil_evaluasi' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
