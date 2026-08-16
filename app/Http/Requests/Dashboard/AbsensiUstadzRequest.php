<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\AbsensiUstadz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AbsensiUstadzRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization delegated to AbsensiUstadzPolicy (create).
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', AbsensiUstadz::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'schedule_id' => $this->input('schedule_id', $this->input('jadwal_id')),
        ]);
    }

    public function rules(): array
    {
        return [
            'schedule_id' => [
                'required',
                'integer',
                Rule::exists('jadwal', 'id')->where('tenant_id', tenant_id()),
            ],
            'tanggal'   => ['required', 'date'],
            'status'    => ['required', Rule::in(AbsensiUstadz::STATUS)],
            'catatan'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status tidak valid.',
            'schedule_id.exists' => 'Jadwal tidak ditemukan.',
        ];
    }
}
