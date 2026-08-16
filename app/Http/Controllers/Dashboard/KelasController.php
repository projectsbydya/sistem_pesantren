<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Program;
use App\Models\Subject;
use App\Services\TenantSetupService;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(string $programSlug)
    {
        $this->authorize('viewAny', Kelas::class);
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $kelasList = Kelas::withCount('santri')
            ->where('program_id', $program->id)
            ->orderBy('name')
            ->get();

        return view('dashboard.kelas.index', compact('kelasList', 'programSlug', 'program'));
    }

    public function create(string $programSlug)
    {
        $this->authorize('create', Kelas::class);
        $program  = Program::where('slug', $programSlug)->firstOrFail();
        $subjects = Subject::where('program_id', $program->id)->orderBy('name')->get();

        return view('dashboard.kelas.create', compact('subjects', 'programSlug', 'program'));
    }

    public function store(Request $request, string $programSlug)
    {
        $this->authorize('create', Kelas::class);
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'subject_ids'   => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $kelas = Kelas::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'program_id'  => $program->id,
        ]);

        if (!empty($data['subject_ids'])) {
            $kelas->subjects()->sync($data['subject_ids']);
        }

        // Refresh onboarding progress after kelas creation
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]))
            ->with('success', "Kelas {$kelas->name} berhasil ditambahkan.");
    }

    public function edit(string $programSlug, int $id)
    {
        $program  = Program::where('slug', $programSlug)->firstOrFail();
        $kelas    = Kelas::with('subjects')->findOrFail($id);
        $this->authorize('update', $kelas);
        $subjects = Subject::where('program_id', $program->id)->orderBy('name')->get();

        return view('dashboard.kelas.edit', compact('kelas', 'subjects', 'programSlug', 'program'));
    }

    public function update(Request $request, string $programSlug, int $id)
    {
        $kelas = Kelas::findOrFail($id);
        $this->authorize('update', $kelas);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'subject_ids'   => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $kelas->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $kelas->subjects()->sync($data['subject_ids'] ?? []);

        // Refresh onboarding progress after kelas update
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]))
            ->with('success', "Kelas {$kelas->name} berhasil diperbarui.");
    }

    public function destroy(string $programSlug, int $id)
    {
        $kelas = Kelas::findOrFail($id);
        $this->authorize('delete', $kelas);
        $name  = $kelas->name;
        $kelas->delete();

        // Refresh onboarding progress after kelas deletion
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.kelas.index', ['programSlug' => $programSlug]))
            ->with('success', "Kelas {$name} berhasil dihapus.");
    }

    /**
     * API: get subjects for a kelas (used by JS in hafalan-nilai flow).
     */
    public function subjects(int $id)
    {
        $kelas = Kelas::with('subjects')->findOrFail($id);
        $this->authorize('view', $kelas);
        return response()->json($kelas->subjects->map(fn ($s) => [
            'id'   => $s->id,
            'name' => $s->name,
            'code' => $s->code,
        ]));
    }
}
