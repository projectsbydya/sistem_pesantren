<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Ustadz;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ScheduleTeacherResolver
{
    public function schedulesForDate(string $date, User $user): Collection
    {
        $day = $this->dayForDate($date);

        return $this->eligibleSchedules()
            ->where('hari', $day)
            ->when($user->isUstadz(), fn ($query) => $query->whereHas(
                'ustadzKelas',
                fn ($assignment) => $assignment->where('ustadz_id', $user->ustadz->id)
            ))
            ->orderBy('program_id')
            ->orderBy('jam_mulai')
            ->get();
    }

    public function schedulesForProgram(Program $program, ?User $user = null): Collection
    {
        return $this->eligibleSchedules()
            ->where('program_id', $program->id)
            ->when($user?->isUstadz(), fn ($query) => $query->whereHas(
                'ustadzKelas',
                fn ($assignment) => $assignment->where('ustadz_id', $user->ustadz->id)
            ))
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();
    }

    public function resolve(
        int $scheduleId,
        ?string $date = null,
        ?int $programId = null,
        string $attribute = 'jadwal_id'
    ): Schedule
    {
        $schedule = $this->eligibleSchedules()
            ->when($date !== null, fn ($query) => $query->where('hari', $this->dayForDate($date)))
            ->when($programId !== null, fn ($query) => $query->where('program_id', $programId))
            ->find($scheduleId);

        if (! $schedule) {
            throw ValidationException::withMessages([
                $attribute => 'Jadwal tidak valid untuk tanggal, program, atau tenant yang dipilih.',
            ]);
        }

        return $schedule;
    }

    public function resolveTeacher(int $scheduleId, string $date): Ustadz
    {
        $schedule = $this->resolve($scheduleId, $date);
        $teacher = $schedule->ustadzKelas?->ustadz;

        if (! $teacher) {
            throw ValidationException::withMessages([
                'schedule_id' => 'Jadwal tidak memiliki ustadz yang valid.',
            ]);
        }

        return $teacher;
    }

    private function eligibleSchedules()
    {
        return Schedule::with([
            'program',
            'ustadzKelas.ustadz.user',
            'ustadzKelas.kelas',
            'ustadzKelas.subject',
        ])->whereHas('program.tenants', function ($tenant) {
            $tenant
                ->where('tenant_id', tenant_id())
                ->where('tenant_programs.is_active', true);
        })->whereHas('ustadzKelas', function ($assignment) {
            $assignment
                ->whereColumn('ustadz_kelas.id', 'jadwal.ustadz_kelas_id')
                ->whereColumn('ustadz_kelas.tenant_id', 'jadwal.tenant_id')
                ->whereHas('ustadz');
        });
    }

    private function dayForDate(string $date): string
    {
        return Schedule::HARI[Carbon::parse($date)->dayOfWeekIso - 1];
    }
}
