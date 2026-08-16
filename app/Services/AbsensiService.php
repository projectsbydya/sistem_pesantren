<?php

namespace App\Services;

use App\Models\AbsensiSantri;
use App\Models\ClassSession;
use App\Models\Program;
use App\Models\Santri;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AbsensiService
{
    /**
     * Get schedules for attendance based on program type and date.
     */
    public function getSchedulesForAttendance(string $type, string $tanggal): Collection
    {
        $carbonDate = \Carbon\Carbon::parse($tanggal);
        $hariIndo = Schedule::HARI[$carbonDate->dayOfWeekIso - 1] ?? null;

        $query = Schedule::with(['ustadzKelas.ustadz.user', 'kelas', 'subject'])
            ->addSelect([
                'active_santri_count' => Santri::selectRaw('count(*)')
                    ->whereColumn('santri.kelas_id', 'jadwal.kelas_id')
                    ->where('santri.status', 'active')
                    ->take(1),
            ])
            ->when($hariIndo, fn ($q) => $q->where('hari', $hariIndo))
            ->whereHas('program', fn ($q) => $q->where('slug', $type))
            ->orderBy('jam_mulai');

        return $query->get();
    }

    /**
     * Get santri list for attendance input.
     */
    public function getSantriList(int $kelasId): Collection
    {
        return Santri::where('status', 'active')
            ->where('kelas_id', $kelasId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get existing attendance records for a specific schedule and date.
     */
    public function getExistingAttendance(int $jadwalId, string $tanggal, string $type): Collection
    {
        return AbsensiSantri::whereHas('program', fn ($q) => $q->where('slug', $type))
            ->where('jadwal_id', $jadwalId)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('santri_id');
    }

    /**
     * Get schedule details for attendance input.
     */
    public function getScheduleForAttendance(int $jadwalId): Schedule
    {
        return Schedule::with(['ustadzKelas.ustadz.user', 'kelas', 'subject'])->findOrFail($jadwalId);
    }

    /**
     * Store bulk attendance records.
     */
    public function storeBulkAttendance(array $absensiData, int $jadwalId, string $tanggal, string $type, ?int $classSessionId = null): void
    {
        $programId = Program::where('slug', $type)->value('id');

        $classSession = $this->resolveClassSession($jadwalId, $tanggal, $classSessionId);

        if (in_array($classSession->status, [ClassSession::STATUS_CANCELLED, ClassSession::STATUS_HOLIDAY], true)) {
            abort(422, 'Tidak dapat mencatat absensi untuk sesi yang dibatalkan atau libur.');
        }

        if ($classSession->status === ClassSession::STATUS_SCHEDULED) {
            $classSession->update(['status' => ClassSession::STATUS_ONGOING, 'started_at' => now()]);
        }

        foreach ($absensiData as $row) {
            AbsensiSantri::updateOrCreate(
                [
                    'program_id' => $programId,
                    'jadwal_id'  => $jadwalId,
                    'santri_id'  => $row['santri_id'],
                    'tanggal'    => $tanggal,
                ],
                [
                    'class_session_id' => $classSession->id,
                    'status'           => $row['status'],
                    'catatan'          => $row['catatan'] ?? null,
                ]
            );
        }
    }

    /**
     * Resolve an existing ClassSession or create one for the schedule/date.
     */
    private function resolveClassSession(int $jadwalId, string $tanggal, ?int $classSessionId): ClassSession
    {
        if ($classSessionId !== null) {
            $classSession = ClassSession::findOrFail($classSessionId);

            if ((int) $classSession->schedule_id !== $jadwalId) {
                abort(422, 'Sesi kelas tidak sesuai dengan jadwal.');
            }

            if ($classSession->session_date->toDateString() !== $tanggal) {
                abort(422, 'Tanggal sesi kelas tidak sesuai.');
            }

            return $classSession;
        }

        $schedule = Schedule::findOrFail($jadwalId);

        return ClassSession::firstOrCreate(
            [
                'schedule_id'  => $jadwalId,
                'session_date' => $tanggal,
            ],
            [
                'program_id' => $schedule->program_id,
                'ustadz_id'  => $schedule->ustadz_id,
                'status'     => ClassSession::STATUS_ONGOING,
                'started_at' => now(),
            ]
        );
    }

    /**
     * Get attendance records for recap.
     */
    public function getAttendanceRecap(int $jadwalId, string $tanggal, string $type): Collection
    {
        return AbsensiSantri::with('santri')
            ->whereHas('program', fn ($q) => $q->where('slug', $type))
            ->where('jadwal_id', $jadwalId)
            ->where('tanggal', $tanggal)
            ->orderBy('santri_id')
            ->get();
    }

    /**
     * Get attendance statistics for a schedule.
     */
    public function getAttendanceStats(int $jadwalId, string $tanggal, string $type): array
    {
        $records = $this->getAttendanceRecap($jadwalId, $tanggal, $type);
        
        $stats = [
            'total' => $records->count(),
            'hadir' => $records->where('status', 'hadir')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'izin' => $records->where('status', 'izin')->count(),
            'alpa' => $records->where('status', 'alpa')->count(),
        ];
        
        return $stats;
    }
}
