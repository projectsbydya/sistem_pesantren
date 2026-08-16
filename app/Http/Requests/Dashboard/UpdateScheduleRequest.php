<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('schedule') ?? Schedule::findOrFail((int) $this->route('id'));

        return $this->user()->can('update', $schedule);
    }

    public function rules(): array
    {
        return [
            'ustadz_kelas_id' => ['required', 'integer', 'exists:ustadz_kelas,id'],
            'mata_pelajaran' => ['required', 'string', 'max:255'],
            'hari'           => ['required', Rule::in(Schedule::HARI)],
            'jam_mulai'      => ['required', 'date_format:H:i'],
            'jam_selesai'    => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'kelas'          => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
