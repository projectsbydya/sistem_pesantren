<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\Program;
use App\Models\Santri;
use App\Services\Academic\AssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(private AssignmentService $assignmentService) {}

    // =====================================================================
    // Index
    // =====================================================================

    public function index(Request $request, string $programSlug): View
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $program = $this->assignmentService->resolveProgram($programSlug);
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);

        $this->authorize('viewAny', Assignment::class);

        $typeConfig = $this->assignmentService->getTypeConfig($type);

        $user = $request->user();
        $isReadOnlyViewer = $user->isStudent() || $user->isParent();

        // Students/parents are read-only: they never get a kelas filter — it
        // would let them browse assignment lists for classes they don't
        // belong to. The service also ignores $kelasId for these roles as a
        // second line of defense, but we don't even render the control.
        $kelasId = (! $isReadOnlyViewer && $request->filled('kelas_id'))
            ? (int) $request->input('kelas_id')
            : null;

        $records = $this->assignmentService->getAssignmentsForProgram($program, $type, $kelasId, $user);
        $kelasList = $isReadOnlyViewer ? collect() : $this->assignmentService->getKelasListForProgram($program);
        $themeList = str_starts_with($type, 'modern-muhadhoroh')
            ? $this->assignmentService->getThemeListForProgram($program)
            : collect();

        $viewMeta = [
            'pack'        => $pack,
            'feature'     => $featureSlug,
            'variant'     => $variant,
            'type'        => $type,
            'typeConfig'  => $typeConfig,
            'title'       => $typeConfig['label'],
            'icon'        => $typeConfig['icon'],
            'columnTitle' => $typeConfig['assignment_fields'][0]['label'] ?? 'Judul',
        ];

        return view('dashboard.assignment.index', compact(
            'program',
            'programSlug',
            'featureSlug',
            'pack',
            'records',
            'kelasList',
            'themeList',
            'kelasId',
            'viewMeta',
            'isReadOnlyViewer'
        ));
    }

    // =====================================================================
    // Store
    // =====================================================================

    public function store(Request $request, string $programSlug): RedirectResponse
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $program = $this->assignmentService->resolveProgram($programSlug);
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);

        $this->authorize('create', Assignment::class);

        $this->assignmentService->createAssignment($request->all(), $type, $program, $request->user());

        $redirect = tenant_route("dashboard.{$pack}.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $variant,
        ]));

        return redirect($redirect)->with('success', 'Tugas berhasil dibuat dan dibagikan ke santri.');
    }

    // =====================================================================
    // Show
    // =====================================================================

    public function show(Request $request, string $programSlug, int $santriId): View
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);

        $program = $this->assignmentService->resolveProgram($programSlug);
        $santri = Santri::findOrFail($santriId);

        $this->authorize('view', $santri);

        $records = $this->assignmentService->getMembersForSantri($santriId, $program, $type, $request->user());
        $typeConfig = $this->assignmentService->getTypeConfig($type);

        $viewMeta = [
            'pack'       => $pack,
            'feature'    => $featureSlug,
            'variant'    => $variant,
            'type'       => $type,
            'typeConfig' => $typeConfig,
            'title'      => $typeConfig['label'],
            'icon'       => $typeConfig['icon'],
        ];

        return view('dashboard.assignment.show', compact(
            'program',
            'programSlug',
            'featureSlug',
            'pack',
            'santri',
            'records',
            'viewMeta'
        ));
    }

    // =====================================================================
    // Edit / Update
    // =====================================================================

    public function edit(Request $request, string $programSlug, int $id): View
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);
        $program = $this->assignmentService->resolveProgram($programSlug);

        // findOrFail is tenant-scoped via HasTenant/TenantScope, but the assignment
        // could still belong to a different program/type within the same tenant —
        // e.g. a stale/forged {id} from another feature's route. Reject that here.
        $assignment = Assignment::where('program_id', $program->id)
            ->where('type', $type)
            ->findOrFail($id);

        $this->authorize('update', $assignment);

        $kelasList = $this->assignmentService->getKelasListForProgram($program);
        $themeList = str_starts_with($type, 'modern-muhadhoroh')
            ? $this->assignmentService->getThemeListForProgram($program)
            : collect();

        $typeConfig = $this->assignmentService->getTypeConfig($type);

        $viewMeta = [
            'pack'       => $pack,
            'feature'    => $featureSlug,
            'variant'    => $variant,
            'type'       => $type,
            'typeConfig' => $typeConfig,
            'title'      => $typeConfig['label'],
            'icon'       => $typeConfig['icon'],
        ];

        return view('dashboard.assignment.edit', compact(
            'program',
            'programSlug',
            'featureSlug',
            'pack',
            'assignment',
            'kelasList',
            'themeList',
            'viewMeta'
        ));
    }

    public function update(Request $request, string $programSlug, int $id): RedirectResponse
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);
        $program = $this->assignmentService->resolveProgram($programSlug);

        $assignment = Assignment::where('program_id', $program->id)
            ->where('type', $type)
            ->findOrFail($id);

        $this->authorize('update', $assignment);

        $this->assignmentService->updateAssignment($assignment, $request->all(), $request->user());

        $redirect = tenant_route("dashboard.{$pack}.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $variant,
        ]));

        return redirect($redirect)->with('success', 'Tugas berhasil diperbarui.');
    }

    // =====================================================================
    // Destroy
    // =====================================================================

    public function destroy(Request $request, string $programSlug, int $id): RedirectResponse
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);
        $program = $this->assignmentService->resolveProgram($programSlug);

        $assignment = Assignment::where('program_id', $program->id)
            ->where('type', $type)
            ->findOrFail($id);

        $this->authorize('delete', $assignment);

        $this->assignmentService->deleteAssignment($assignment);

        $redirect = tenant_route("dashboard.{$pack}.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $variant,
        ]));

        return redirect($redirect)->with('success', 'Tugas berhasil dihapus.');
    }

    // =====================================================================
    // Member update
    // =====================================================================

    public function updateMember(Request $request, string $programSlug, int $memberId): RedirectResponse|JsonResponse
    {
        $pack = $request->route('pack');
        $featureSlug = $request->route('featureSlug');
        $variant = $request->query('type');
        $type = $this->assignmentService->resolveType($pack, $featureSlug, $variant);
        $program = $this->assignmentService->resolveProgram($programSlug);

        $member = AssignmentMember::whereHas(
            'assignment',
            fn ($q) => $q->where('program_id', $program->id)->where('type', $type)
        )->findOrFail($memberId);

        $this->authorize('update', $member);

        $this->assignmentService->updateMember($member, $request->all(), $request->user());

        // The member-progress form in dashboard.assignment.index is a plain HTML
        // form submit (no JS/fetch), so a normal browser request must get a
        // redirect back — only JSON/AJAX callers get a JSON body.
        if (! $request->wantsJson()) {
            $redirect = tenant_route("dashboard.{$pack}.{$featureSlug}.index", array_filter([
                'programSlug' => $programSlug,
                'type'        => $variant,
            ]));

            return redirect($redirect)->with('success', 'Progress santri berhasil diperbarui.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Progress santri berhasil diperbarui.',
            'member'  => $member->fresh(['santri', 'assignment']),
        ]);
    }
}
