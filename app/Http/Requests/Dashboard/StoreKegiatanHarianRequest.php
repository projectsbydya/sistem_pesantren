<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\KegiatanHarian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreKegiatanHarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', KegiatanHarian::class);
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'integer', 'exists:santri,id'],
            'jenis_kegiatan' => ['required', Rule::in(KegiatanHarian::JENIS_OPTIONS)],
            'kategori' => ['nullable', Rule::in(KegiatanHarian::KATEGORI_OPTIONS)],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:waktu_mulai'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
