<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Program;
use Illuminate\Support\Collection;

class ClassSessionService
{
    public function __construct(private ScheduleTeacherResolver $scheduleTeacherResolver) {}
    /**
     * Resolve an active program the tenant can access.
     */
    public function resolveProgram(string $programSlug): Program
    {
        $tenantId = (int) tenant_id();

        return Program::whereHas('tenants', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('tenant_programs.is_active', true);
        })->where('slug', $programSlug)->firstOrFail();
    }

    /**
     * Get class sessions for a program, optionally filtered.
     */
    public function getClassSessionsForProgram(Program $program, array $filters = []): Collection
    {
        $query = ClassSession::with(['schedule.ustadzKelas.ustadz.user', 'schedule.ustadzKelas.kelas', 'schedule.ustadzKelas.subject', 'ustadz.user'])
            ->byProgram($program->id);

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->byDate($filters['date']);
        } elseif (!empty($filters['start']) && !empty($filters['end'])) {
            $query->byDateRange($filters['start'], $filters['end']);
        }

        return $query->orderBy('session_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a single class session.
     */
    public function getClassSession(int $id): ?ClassSession
    {
        return ClassSession::with(['schedule.ustadzKelas.ustadz.user', 'schedule.ustadzKelas.kelas', 'schedule.ustadzKelas.subject', 'ustadz.user'])
            ->find($id);
    }

    /**
     * Get schedules eligible for creating a class session in the program.
     */
    public function getSchedulesForProgram(Program $program): Collection
    {
        return $this->scheduleTeacherResolver->schedulesForProgram($program, auth()->user());
    }

    /**
     * Store a new class session.
     */
    public function storeClassSession(array $data): ClassSession
    {
        $schedule = $this->scheduleTeacherResolver->resolve(
            (int) $data['schedule_id'],
            $data['session_date'],
            (int) $data['program_id'],
            'schedule_id'
        );

        return ClassSession::create([
            'tenant_id'    => tenant_id(),
            'program_id'   => $data['program_id'],
            'schedule_id'  => $schedule->id,
            'ustadz_id'    => $schedule->ustadzKelas?->ustadz_id,
            'session_date' => $data['session_date'],
            'status'       => $data['status'] ?? ClassSession::STATUS_SCHEDULED,
            'started_at'   => $data['started_at'] ?? null,
            'ended_at'     => $data['ended_at'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update an existing class session.
     */
    public function updateClassSession(ClassSession $classSession, array $data): ClassSession
    {
        $update = [
            'session_date' => $data['session_date'],
            'status'       => $data['status'] ?? $classSession->status,
            'started_at'   => $data['started_at'] ?? null,
            'ended_at'     => $data['ended_at'] ?? null,
            'notes'        => $data['notes'] ?? null,
        ];

        $schedule = $this->scheduleTeacherResolver->resolve(
            (int) $data['schedule_id'],
            $data['session_date'],
            (int) $classSession->program_id,
            'schedule_id'
        );
        $update['schedule_id'] = $schedule->id;
        $update['ustadz_id'] = $schedule->ustadzKelas->ustadz_id;

        $classSession->update($update);

        return $classSession->fresh();
    }

    /**
     * Delete a class session.
     */
    public function deleteClassSession(ClassSession $classSession): bool
    {
        return $classSession->delete();
    }
}
