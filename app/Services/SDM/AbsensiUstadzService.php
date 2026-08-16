<?php

namespace App\Services\SDM;

use App\Models\AbsensiUstadz;
use App\Models\Schedule;
use App\Models\Ustadz;
use App\Services\ScheduleTeacherResolver;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AbsensiUstadzService
{
    public function __construct(private ScheduleTeacherResolver $scheduleTeacherResolver) {}

    public function getEligibleSchedulesForDate(string $tanggal): Collection
    {
        return $this->scheduleTeacherResolver->schedulesForDate($tanggal, auth()->user());
    }

    public function resolveSchedule(int $scheduleId, string $tanggal): Schedule
    {
        return $this->scheduleTeacherResolver->resolve($scheduleId, $tanggal, null, 'schedule_id');
    }

    public function findAttendance(int $scheduleId, string $tanggal): ?AbsensiUstadz
    {
        return AbsensiUstadz::with(['schedule', 'ustadz.user', 'ustadz.subjects'])
            ->where('schedule_id', $scheduleId)
            ->where('tanggal', $tanggal)
            ->first();
    }

    public function resolveTeacherForAttendance(
        int $scheduleId,
        string $tanggal,
        ?AbsensiUstadz $attendance = null
    ): Ustadz {
        if ($attendance !== null) {
            return $attendance->ustadz->loadMissing('subjects');
        }

        return $this->scheduleTeacherResolver->resolveTeacher($scheduleId, $tanggal)->loadMissing('subjects');
    }

    /**
     * Store or update single ustadz attendance record.
     */
    public function saveAttendance(
        int $scheduleId,
        string $tanggal,
        string $status,
        ?string $catatan = null
    ): void {
        $attendance = $this->findAttendance($scheduleId, $tanggal);

        if ($attendance !== null) {
            $this->updateAttendance($attendance, $status, $catatan);

            return;
        }

        $this->createAttendance($scheduleId, $tanggal, $status, $catatan);
    }

    /**
     * Store bulk attendance records.
     */
    public function saveBulkAttendance(array $absensiData, int $scheduleId, string $tanggal): void
    {
        if (count($absensiData) !== 1) {
            throw ValidationException::withMessages([
                'absensi' => 'Satu jadwal hanya dapat memiliki satu record absensi ustadz.',
            ]);
        }

        $row = reset($absensiData);

        $this->saveAttendance(
            $scheduleId,
            $tanggal,
            $row['status'],
            $row['catatan'] ?? null
        );
    }

    /**
     * Get attendance recap for a specific date.
     */
    public function getAttendanceRecap(string $tanggal): Collection
    {
        $user = auth()->user();

        // Relation-based filtering: USER -> Ustadz -> AbsensiUstadz
        return AbsensiUstadz::with(['schedule.program', 'ustadz.user', 'ustadz.subjects'])
            ->where('tanggal', $tanggal)
            ->when($user->ustadz, function ($query) use ($user) {
                // Ustadz: only their own records
                return $query->where('ustadz_id', $user->ustadz->id);
            })
            ->orderBy('ustadz_id')
            ->get();
    }

    /**
     * Get attendance summary grouped by status.
     */
    public function getAttendanceSummary(string $tanggal): Collection
    {
        $records = $this->getAttendanceRecap($tanggal);
        return $records->groupBy('status')->map->count();
    }

    private function createAttendance(
        int $scheduleId,
        string $tanggal,
        string $status,
        ?string $catatan
    ): void {
        $ustadz = $this->scheduleTeacherResolver->resolveTeacher($scheduleId, $tanggal);

        AbsensiUstadz::create([
            'schedule_id' => $scheduleId,
            'ustadz_id' => $ustadz->id,
            'tanggal' => $tanggal,
            'status' => $status,
            'catatan' => $catatan,
        ]);
    }

    private function updateAttendance(
        AbsensiUstadz $attendance,
        string $status,
        ?string $catatan
    ): void {
        $attendance->status = $status;
        $attendance->catatan = $catatan;
        $attendance->save();
    }
}
