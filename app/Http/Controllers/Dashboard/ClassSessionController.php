<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Program;
use App\Models\Schedule;
use App\Services\ClassSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassSessionController extends Controller
{
    public function __construct(private ClassSessionService $classSessionService) {}

    public function index(Request $request, string $programSlug): View
    {
        $this->authorize('viewAny', ClassSession::class);

        $program = $this->classSessionService->resolveProgram($programSlug);
        $filters = [
            'status' => $request->query('status'),
            'date'   => $request->query('date'),
            'start'  => $request->query('start'),
            'end'    => $request->query('end'),
        ];

        $user     = auth()->user();
        $sessions = $this->classSessionService->getClassSessionsForProgram($program, array_filter($filters))
            ->filter(fn ($s) => $user->can('view', $s))
            ->values();

        return view('dashboard.class-sessions.index', compact(
            'program', 'programSlug', 'sessions', 'filters'
        ));
    }

    public function create(string $programSlug): View
    {
        $this->authorize('create', ClassSession::class);

        $program   = $this->classSessionService->resolveProgram($programSlug);
        $user      = auth()->user();
        $schedules = $this->classSessionService->getSchedulesForProgram($program);

        if ($user->isUstadz()) {
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            $schedules = $schedules->filter(fn ($s) => $assignedIds->contains($s->ustadz_kelas_id))->values();
        }

        return view('dashboard.class-sessions.create', compact('program', 'programSlug', 'schedules'));
    }

    public function store(Request $request, string $programSlug): RedirectResponse
    {
        $this->authorize('create', ClassSession::class);

        $program = $this->classSessionService->resolveProgram($programSlug);
        $user    = auth()->user();

        $data = $request->validate($this->rules($program));
        $data['program_id'] = $program->id;

        $schedule = Schedule::findOrFail($data['schedule_id']);
        if ($user->isUstadz() && (int) $schedule->ustadzKelas?->ustadz_id !== (int) $user->ustadz->id) {
            abort(403);
        }

        $this->classSessionService->storeClassSession($data);

        return redirect(tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]))
            ->with('success', 'Sesi kelas berhasil ditambahkan.');
    }

    public function edit(string $programSlug, int $id): View
    {
        $session = ClassSession::findOrFail($id);
        $this->authorize('update', $session);

        $program = $this->classSessionService->resolveProgram($programSlug);
        if ($session->program_id !== $program->id) {
            abort(404);
        }

        $user      = auth()->user();
        $schedules = $this->classSessionService->getSchedulesForProgram($program);

        if ($user->isUstadz()) {
            $assignedIds = $user->ustadz->ustadzKelas()->pluck('id');
            $schedules = $schedules->filter(fn ($s) => $assignedIds->contains($s->ustadz_kelas_id))->values();
        }

        return view('dashboard.class-sessions.edit', compact('program', 'programSlug', 'session', 'schedules'));
    }

    public function update(Request $request, string $programSlug, int $id): RedirectResponse
    {
        $session = ClassSession::findOrFail($id);
        $this->authorize('update', $session);

        $program = $this->classSessionService->resolveProgram($programSlug);
        if ($session->program_id !== $program->id) {
            abort(404);
        }

        $user = auth()->user();
        $data = $request->validate($this->rules($program, $session->id));

        $schedule = Schedule::findOrFail($data['schedule_id']);
        if ($user->isUstadz() && (int) $schedule->ustadzKelas?->ustadz_id !== (int) $user->ustadz->id) {
            abort(403);
        }

        $this->classSessionService->updateClassSession($session, $data);

        return redirect(tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]))
            ->with('success', 'Sesi kelas berhasil diperbarui.');
    }

    public function destroy(string $programSlug, int $id): RedirectResponse
    {
        $session = ClassSession::findOrFail($id);
        $this->authorize('delete', $session);

        $program = $this->classSessionService->resolveProgram($programSlug);
        if ($session->program_id !== $program->id) {
            abort(404);
        }

        $this->classSessionService->deleteClassSession($session);

        return redirect(tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]))
            ->with('success', 'Sesi kelas berhasil dihapus.');
    }

    private function rules(Program $program, ?int $excludeId = null): array
    {
        return [
            'schedule_id'  => ['required', 'integer', Rule::exists('jadwal', 'id')->where('tenant_id', tenant_id())->where('program_id', $program->id)],
            'session_date' => ['required', 'date'],
            'status'       => ['required', Rule::in(ClassSession::STATUS)],
            'started_at'   => ['nullable', 'date'],
            'ended_at'     => ['nullable', 'date', 'after_or_equal:started_at'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}
