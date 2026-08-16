<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\AbsensiSantri;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BulkAbsensiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization delegated to AbsensiPolicy (viewAny/create).
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', AbsensiSantri::class);
    }

    public function rules(): array
    {
        return [
            'jadwal_id'          => ['required', 'integer', 'exists:jadwal,id'],
            'tanggal'            => ['required', 'date'],
            'absensi'            => ['required', 'array', 'min:1'],
            'absensi.*.santri_id' => ['required', 'integer', 'exists:santri,id'],
            'absensi.*.status'    => ['required', Rule::in(AbsensiSantri::STATUS)],
            'absensi.*.catatan'   => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'absensi.*.status.in' => 'Status tidak valid.',
            'absensi.*.santri_id.exists' => 'Santri tidak ditemukan.',
        ];
    }
}
