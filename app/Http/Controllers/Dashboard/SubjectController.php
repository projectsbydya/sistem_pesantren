<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\TenantSetupService;
use Illuminate\Http\Request;

class SubjectController extends Controller
{

    public function index($programSlug)
    {
        // Get program by slug
        $program = \App\Models\Program::bySlug($programSlug)->firstOrFail();
        $programId = $program->id;

        // Policy-based access control
        $this->authorize('viewAny', [Subject::class, $programSlug]);

        $subjects = Subject::orderBy('name')
            ->byProgram($programId)
            ->get();

        return view('dashboard.mata-pelajaran.index', compact('subjects', 'programSlug', 'programId'));
    }

    public function create($programSlug)
    {
        // Get program by slug
        $program = \App\Models\Program::bySlug($programSlug)->firstOrFail();
        $programId = $program->id;

        // Policy-based access control
        $this->authorize('create', [Subject::class, $programSlug]);

        return view('dashboard.mata-pelajaran.create', compact('programSlug', 'programId'));
    }

    public function store(Request $request, $programSlug)
    {
        // Get program by slug
        $program = \App\Models\Program::bySlug($programSlug)->firstOrFail();
        $programId = $program->id;

        // Policy-based access control
        $this->authorize('create', [Subject::class, $programSlug]);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Add tenant_id and program_id
        $data['tenant_id'] = (int) auth()->user()->tenant_id;
        $data['program_id'] = $programId;

        // Unique name per tenant + program (DB has unique(tenant_id, name))
        $exists = Subject::where('tenant_id', $data['tenant_id'])
            ->where('name', $data['name'])
            ->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'Mata pelajaran "' . $data['name'] . '" sudah ada.');
        }

        Subject::create($data);

        // Refresh onboarding progress after subject creation
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.subjects.index', ['programSlug' => $programSlug]))
            ->with('success', 'Mata pelajaran "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function edit($programSlug, $id)
    {
        // Get program by slug
        $program = \App\Models\Program::bySlug($programSlug)->firstOrFail();
        $programId = $program->id;

        $subject = Subject::findOrFail((int) $id);
        
        // Policy-based access control
        $this->authorize('update', $subject);

        return view('dashboard.mata-pelajaran.edit', compact('subject', 'programSlug', 'programId'));
    }

    public function update(Request $request, $programSlug, $id)
    {
        // Get program by slug
        $program = \App\Models\Program::bySlug($programSlug)->firstOrFail();
        $programId = $program->id;

        $subject = Subject::findOrFail((int) $id);
        
        // Policy-based access control
        $this->authorize('update', $subject);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Unique name per tenant + program, exclude self
        $exists = Subject::where('tenant_id', $subject->tenant_id)
            ->where('program_id', $subject->program_id)
            ->where('name', $data['name'])
            ->where('id', '!=', $subject->id)
            ->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'Mata pelajaran "' . $data['name'] . '" sudah ada.');
        }

        $subject->update($data);

        // Refresh onboarding progress after subject update
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.subjects.index', ['programSlug' => $programSlug]))
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy($programSlug, $id)
    {
        // Get program by slug
        $program = \App\Models\Program::bySlug($programSlug)->firstOrFail();
        $programId = $program->id;

        $subject = Subject::findOrFail((int) $id);
        $name    = $subject->name;
        
        // Policy-based access control
        $this->authorize('delete', $subject);
        
        $subject->delete();

        // Refresh onboarding progress after subject deletion
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.akademik.subjects.index', ['programSlug' => $programSlug]))
            ->with('success', 'Mata pelajaran "' . $name . '" berhasil dihapus.');
    }
}
