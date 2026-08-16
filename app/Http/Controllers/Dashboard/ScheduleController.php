<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\UstadzKelas;
use App\Services\FeatureDependencyService;
use App\Services\TenantSetupService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{

    public function index(string $programSlug)
    {
        $this->authorize('viewAny', Schedule::class);

        // Check dependencies - show warning if missing
        $dependencyCheck = FeatureDependencyService::validateCreateSchedule();
        if (!$dependencyCheck['can_access']) {
            return view('dashboard.jadwal.index', [
                'warning' => $dependencyCheck,
                'jadwal' => collect(),
                'grouped' => collect(),
                'programSlug' => $programSlug,
                'program' => Program::where('slug', $programSlug)->firstOrFail(),
            ]);
        }

        $program   = Program::where('slug', $programSlug)->firstOrFail();
        $user      = auth()->user();
        $hariOrder = array_flip(Schedule::HARI);

        $jadwal = Schedule::with(['ustadzKelas.ustadz.user', 'ustadzKelas.kelas', 'ustadzKelas.subject'])
            ->where('program_id', $program->id)
            ->orderBy('jam_mulai')
            ->get()
            ->filter(fn ($j) => $user->can('view', $j))
            ->sortBy(fn ($j) => $hariOrder[$j->hari] ?? 99)
            ->values();

        $grouped = $jadwal->groupBy('hari');

        return view('dashboard.jadwal.index', compact('jadwal', 'grouped', 'programSlug', 'program'));
    }

    public function create(string $programSlug)
    {
        $this->authorize('create', Schedule::class);

        // Check dependencies - show warning if missing
        $dependencyCheck = FeatureDependencyService::validateCreateSchedule();
        if (!$dependencyCheck['can_access']) {
            return view('components.dependency-warning', $dependencyCheck);
        }

        $program     = Program::where('slug', $programSlug)->firstOrFail();
        $user        = auth()->user();
        $ustadzKelas = UstadzKelas::with(['ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->when($user->isUstadz(), function ($query) use ($user) {
                $query->where('ustadz_id', $user->ustadz->id);
            })
            ->get();

        $canCreateAssignment = $user->can('create', UstadzKelas::class);

        if ($canCreateAssignment) {
            $kelas    = \App\Models\Kelas::where('program_id', $program->id)->with('subjects')->orderBy('name')->get();
            $subjects = \App\Models\Subject::where('program_id', $program->id)->orderBy('name')->get();
            $ustadzList = \App\Models\Ustadz::with('user')->where('tenant_id', tenant_id())->orderBy('id')->get();
        } else {
            $kelas    = $ustadzKelas->pluck('kelas')->unique('id')->sortBy('name')->values();
            $subjects = $ustadzKelas->pluck('subject')->unique('id')->sortBy('name')->values();
            $ustadzList = $ustadzKelas->pluck('ustadz')->unique('id')->sortBy(fn ($u) => $u->user?->name ?? '')->values();
        }

        $kelasSubjectsMap = $kelas->mapWithKeys(fn ($k) => [$k->id => $k->subjects->pluck('id')->all()])->all();

        return view('dashboard.jadwal.create', compact('ustadzKelas', 'kelas', 'subjects', 'kelasSubjectsMap', 'ustadzList', 'canCreateAssignment', 'programSlug', 'program'));
    }

    public function store(Request $request, string $programSlug)
    {
        $this->authorize('create', Schedule::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $user    = auth()->user();

        $data = $request->validate([
            'ustadz_kelas_id' => [
                'required',
                'integer',
                Rule::exists('ustadz_kelas', 'id')->where('tenant_id', tenant_id())->where('program_id', $program->id),
            ],
            'hari'            => ['required', 'in:' . implode(',', Schedule::HARI)],
            'jam_mulai'       => ['required', 'date_format:H:i'],
            'jam_selesai'     => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $ustadzKelas = UstadzKelas::findOrFail($data['ustadz_kelas_id']);

        if ($user->isUstadz() && (int) $ustadzKelas->ustadz_id !== (int) $user->ustadz->id) {
            abort(403);
        }

        $conflict = Schedule::conflicting(
            $data['ustadz_kelas_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai']
        )->first();

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Jadwal bentrok dengan \"{$conflict->mata_pelajaran}\" ({$conflict->jam_mulai}–{$conflict->jam_selesai}) di hari yang sama."
            );
        }

        Schedule::create([
            'ustadz_kelas_id' => $ustadzKelas->id,
            'kelas_id'        => $ustadzKelas->kelas_id,
            'program_id'      => $program->id,
            'mata_pelajaran'  => $ustadzKelas->subject?->name ?? '—',
            'kelas'           => $ustadzKelas->kelas?->name ?? '—',
            'hari'            => $data['hari'],
            'jam_mulai'       => $data['jam_mulai'],
            'jam_selesai'     => $data['jam_selesai'],
        ]);

        // Refresh onboarding progress after jadwal creation
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]))
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(string $programSlug, $id)
    {
        $schedule = Schedule::with('ustadzKelas')->findOrFail((int) $id);
        $this->authorize('update', $schedule);

        $program     = Program::where('slug', $programSlug)->firstOrFail();
        $user        = auth()->user();
        $ustadzKelas = UstadzKelas::with(['ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->when($user->isUstadz(), function ($query) use ($user) {
                $query->where('ustadz_id', $user->ustadz->id);
            })
            ->get();

        $canCreateAssignment = $user->can('create', UstadzKelas::class);

        if ($canCreateAssignment) {
            $kelas    = \App\Models\Kelas::where('program_id', $program->id)->with('subjects')->orderBy('name')->get();
            $subjects = \App\Models\Subject::where('program_id', $program->id)->orderBy('name')->get();
            $ustadzList = \App\Models\Ustadz::with('user')->where('tenant_id', tenant_id())->orderBy('id')->get();
        } else {
            $kelas    = $ustadzKelas->pluck('kelas')->unique('id')->sortBy('name')->values();
            $subjects = $ustadzKelas->pluck('subject')->unique('id')->sortBy('name')->values();
            $ustadzList = $ustadzKelas->pluck('ustadz')->unique('id')->sortBy(fn ($u) => $u->user?->name ?? '')->values();
        }

        $kelasSubjectsMap = $kelas->mapWithKeys(fn ($k) => [$k->id => $k->subjects->pluck('id')->all()])->all();

        return view('dashboard.jadwal.edit', compact('schedule', 'ustadzKelas', 'kelas', 'subjects', 'kelasSubjectsMap', 'ustadzList', 'canCreateAssignment', 'programSlug', 'program'));
    }

    public function update(Request $request, string $programSlug, $id)
    {
        $schedule = Schedule::findOrFail((int) $id);
        $this->authorize('update', $schedule);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $user    = auth()->user();

        $data = $request->validate([
            'ustadz_kelas_id' => [
                'required',
                'integer',
                Rule::exists('ustadz_kelas', 'id')->where('tenant_id', tenant_id())->where('program_id', $program->id),
            ],
            'hari'            => ['required', 'in:' . implode(',', Schedule::HARI)],
            'jam_mulai'       => ['required', 'date_format:H:i'],
            'jam_selesai'     => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $ustadzKelas = UstadzKelas::findOrFail($data['ustadz_kelas_id']);

        if ($user->isUstadz() && (int) $ustadzKelas->ustadz_id !== (int) $user->ustadz->id) {
            abort(403);
        }

        $conflict = Schedule::conflicting(
            $data['ustadz_kelas_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $schedule->id
        )->first();

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Jadwal bentrok dengan \"{$conflict->mata_pelajaran}\" ({$conflict->jam_mulai}–{$conflict->jam_selesai}) di hari yang sama."
            );
        }

        $schedule->update([
            'ustadz_kelas_id' => $ustadzKelas->id,
            'kelas_id'        => $ustadzKelas->kelas_id,
            'mata_pelajaran'  => $ustadzKelas->subject?->name ?? $schedule->mata_pelajaran,
            'kelas'           => $ustadzKelas->kelas?->name ?? $schedule->kelas,
            'hari'            => $data['hari'],
            'jam_mulai'       => $data['jam_mulai'],
            'jam_selesai'     => $data['jam_selesai'],
        ]);

        // Refresh onboarding progress after jadwal update
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]))
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(string $programSlug, $id)
    {
        $schedule = Schedule::findOrFail((int) $id);
        $this->authorize('delete', $schedule);
        $schedule->delete();

        // Refresh onboarding progress after jadwal deletion
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]))
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}
