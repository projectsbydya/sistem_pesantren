<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use App\Services\TenantSetupService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UstadzKelasController extends Controller
{
    public function index(string $programSlug)
    {
        $this->authorize('viewAny', UstadzKelas::class);
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $penugasan = UstadzKelas::with(['ustadz.user', 'kelas', 'subject'])
            ->where('program_id', $program->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.penugasan.index', compact('penugasan', 'programSlug', 'program'));
    }

    public function create(string $programSlug)
    {
        $this->authorize('create', UstadzKelas::class);
        $program  = Program::where('slug', $programSlug)->firstOrFail();
        $ustadz   = Ustadz::with('user')->orderBy('id')->get();
        // Load kelas dengan subjects untuk filtering di frontend
        $kelasList = Kelas::where('program_id', $program->id)
            ->with('subjects')
            ->orderBy('name')
            ->get();

        return view('dashboard.penugasan.create', compact('program', 'programSlug', 'ustadz', 'kelasList'));
    }

    public function store(Request $request, string $programSlug)
    {
        $this->authorize('create', UstadzKelas::class);
        $program = Program::where('slug', $programSlug)->firstOrFail();

        try {
            $this->resolveOrCreateAssignment($request, $program, true);
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first());
        }

        // Refresh onboarding progress after penugasan creation
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => $programSlug]))
            ->with('success', 'Penugasan berhasil ditambahkan.');
    }

    /**
     * AJAX endpoint used by the Schedule form to ensure a teaching assignment
     * exists for the selected ustadz + kelas + subject. Creates it on demand.
     */
    public function ensure(Request $request, string $programSlug)
    {
        $this->authorize('create', UstadzKelas::class);
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $result = $this->resolveOrCreateAssignment($request, $program);

        return response()->json([
            'ustadz_kelas_id' => $result['assignment']->id,
            'created'         => $result['created'],
        ]);
    }

    /**
     * Resolve an existing UstadzKelas record or create one if missing.
     * Reuses the same validation, program-ownership and tenant checks.
     */
    private function resolveOrCreateAssignment(Request $request, Program $program, bool $failOnDuplicate = false): array
    {
        $data = $request->validate([
            'ustadz_id'  => [
                'required',
                'integer',
                Rule::exists('ustadz', 'id')->where('tenant_id', tenant_id()),
            ],
            'kelas_id'   => ['required', 'integer', 'exists:kelas,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $validation = UstadzKelas::validateProgramOwnership(
            $program->id,
            $data['kelas_id'],
            $data['subject_id'],
            tenant_id()
        );

        if (!$validation['valid']) {
            throw ValidationException::withMessages(['general' => implode(' ', $validation['errors'])]);
        }

        $assignment = UstadzKelas::where('tenant_id', tenant_id())
            ->where('program_id', $program->id)
            ->where('ustadz_id', $data['ustadz_id'])
            ->where('kelas_id', $data['kelas_id'])
            ->where('subject_id', $data['subject_id'])
            ->first();

        if ($assignment) {
            if ($failOnDuplicate) {
                throw ValidationException::withMessages(['general' => 'Penugasan ini sudah ada.']);
            }

            return ['assignment' => $assignment, 'created' => false];
        }

        $assignment = UstadzKelas::create([
            'tenant_id'  => tenant_id(),
            'program_id' => $program->id,
            'ustadz_id'  => $data['ustadz_id'],
            'kelas_id'   => $data['kelas_id'],
            'subject_id' => $data['subject_id'],
        ]);

        return ['assignment' => $assignment, 'created' => true];
    }

    public function edit(string $programSlug, int $id)
    {
        $penugasan  = UstadzKelas::with(['kelas.subjects'])->findOrFail($id);
        $this->authorize('update', $penugasan);
        $program    = Program::where('slug', $programSlug)->firstOrFail();
        $ustadz     = Ustadz::with('user')->orderBy('id')->get();
        $kelasList  = Kelas::where('program_id', $program->id)
            ->with('subjects')
            ->orderBy('name')
            ->get();

        return view('dashboard.penugasan.edit', compact('penugasan', 'program', 'programSlug', 'ustadz', 'kelasList'));
    }

    public function update(Request $request, string $programSlug, int $id)
    {
        $penugasan = UstadzKelas::findOrFail($id);
        $this->authorize('update', $penugasan);
        $program   = Program::where('slug', $programSlug)->firstOrFail();

        // Tenant isolation: ensure penugasan belongs to this tenant
        if ($penugasan->tenant_id !== tenant_id()) {
            abort(403, 'Akses ditolak.');
        }

        $data = $request->validate([
            'ustadz_id'  => ['required', 'integer', 'exists:ustadz,id'],
            'kelas_id'   => ['required', 'integer', 'exists:kelas,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        // Validate: subject dan kelas harus milik program yang sama
        $validation = UstadzKelas::validateProgramOwnership(
            $program->id,
            $data['kelas_id'],
            $data['subject_id'],
            tenant_id()
        );

        if (!$validation['valid']) {
            return back()->withInput()->with('error', implode(' ', $validation['errors']));
        }

        // Cek duplikat
        $exists = UstadzKelas::where('program_id', $program->id)
            ->where('ustadz_id', $data['ustadz_id'])
            ->where('kelas_id', $data['kelas_id'])
            ->where('subject_id', $data['subject_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Penugasan ini sudah ada.');
        }

        $penugasan->update([
            'ustadz_id'  => $data['ustadz_id'],
            'kelas_id'   => $data['kelas_id'],
            'subject_id' => $data['subject_id'],
        ]);

        // Refresh onboarding progress after penugasan update
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => $programSlug]))
            ->with('success', 'Penugasan berhasil diperbarui.');
    }

    public function destroy(string $programSlug, int $id)
    {
        $penugasan = UstadzKelas::findOrFail($id);
        $this->authorize('delete', $penugasan);

        // Tenant isolation: ensure penugasan belongs to this tenant
        if ($penugasan->tenant_id !== tenant_id()) {
            abort(403, 'Akses ditolak.');
        }

        $penugasan->delete();

        // Refresh onboarding progress after penugasan deletion
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.penugasan.index', ['programSlug' => $programSlug]))
            ->with('success', 'Penugasan berhasil dihapus.');
    }
}
