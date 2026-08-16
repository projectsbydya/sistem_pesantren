<?php

declare(strict_types=1);

namespace App\Http\Requests\Dashboard;

use App\Models\Perizinan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePerizinanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Perizinan::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('perizinan') instanceof Perizinan) {
            $this->merge([
                'santri_id' => $this->route('perizinan')->santri_id,
            ]);
        }
    }

    public function rules(): array
    {
        $user = $this->user();

        $santriIdRules = [
            'required',
            'integer',
            Rule::exists('santri', 'id')->where('tenant_id', tenant_id()),
        ];

        if ($this->isMethod('POST')) {
            $santriIdRules[] = function ($attribute, $value, $fail) use ($user) {
                if ($user->santri !== null && (int) $value !== (int) $user->santri->id) {
                    $fail('Santri hanya dapat mengajukan izin untuk diri sendiri.');
                }

                if ($user->parent !== null) {
                    $hasChild = $user->parent->santri()->where('santri.id', $value)->exists();
                    if (! $hasChild) {
                        $fail('Anda hanya dapat mengajukan izin untuk anak yang terdaftar.');
                    }
                }
            };
        }

        return [
            'santri_id' => $santriIdRules,
            'jenis' => ['required', Rule::in(Perizinan::JENIS_OPTIONS)],
            'alasan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'destinasi' => ['nullable', 'string', 'max:255'],
            'penjemput' => ['nullable', 'string', 'max:255'],
            'no_hp_penjemput' => ['nullable', 'string', 'max:20'],
        ];
    }
}
