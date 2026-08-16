<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\UstadzKelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $hari  = fake()->randomElement(Schedule::HARI);
        $start = fake()->time('H:i');
        $end   = date('H:i', strtotime($start . ' +1 hour'));

        $ustadzKelas = UstadzKelas::factory()->create();

        return [
            'tenant_id'       => $ustadzKelas->tenant_id,
            'program_id'      => $ustadzKelas->program_id,
            'ustadz_kelas_id' => $ustadzKelas->id,
            'kelas_id'        => $ustadzKelas->kelas_id,
            'subject_id'      => $ustadzKelas->subject_id,
            'mata_pelajaran'  => $ustadzKelas->subject?->name ?? fake()->words(2, true),
            'kelas'           => $ustadzKelas->kelas?->name ?? fake()->words(2, true),
            'hari'            => $hari,
            'jam_mulai'       => $start,
            'jam_selesai'     => $end,
        ];
    }
}
