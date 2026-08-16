<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\KegiatanHarian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateKegiatanHarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kegiatan = $this->route('kegiatan');
        return $this->user()->can('update', $kegiatan);
    }

    public function rules(): array
    {
        return [
            'jenis_kegiatan' => ['required', Rule::in(KegiatanHarian::JENIS_OPTIONS)],
            'kategori' => ['nullable', Rule::in(KegiatanHarian::KATEGORI_OPTIONS)],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:waktu_mulai'],
            'status' => ['nullable', Rule::in(KegiatanHarian::STATUS_OPTIONS)],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
