<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\MonitoringKarakter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMonitoringKarakterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MonitoringKarakter::class);
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'aspek' => ['required', Rule::in(MonitoringKarakter::ASPEK_OPTIONS)],
            'skor' => ['required', 'integer', 'min:0', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
            'periode' => ['nullable', 'string', 'max:50'],
        ];
    }
}
