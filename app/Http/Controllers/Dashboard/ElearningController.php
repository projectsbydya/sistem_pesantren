<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Elearning;
use App\Models\Kelas;
use App\Models\Subject;
use App\Services\ElearningService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ElearningController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ElearningService $elearningService) {}

    /**
     * List e-learning materials scoped to the current user's assignments.
     */
    public function index(Request $request, string $programSlug)
    {
        $this->authorize('viewAny', Elearning::class);

        $user      = auth()->user();
        $materials = $this->elearningService->accessibleQuery($user, $programSlug)->latest()->get();

        return view('dashboard.elearning.index', compact('materials', 'programSlug'));
    }

    /**
     * Create form.
     */
    public function create(Request $request, string $programSlug)
    {
        $this->authorize('create', Elearning::class);

        $program  = \App\Models\Program::where('slug', $programSlug)->firstOrFail();
        $user     = auth()->user();
        $subjects = Subject::where('program_id', $program->id)->orderBy('name')->get();
        $kelas    = Kelas::where('program_id', $program->id)->orderBy('name')->get();

        // Admin sees all ustadz_kelas; ustadz sees only their own
        $ustadzKelasList = $this->elearningService->accessibleUstadzKelas($user, $programSlug);

        return view('dashboard.elearning.create', compact('programSlug', 'subjects', 'kelas', 'ustadzKelasList'));
    }

    /**
     * Store new material via ElearningService.
     */
    public function store(Request $request, string $programSlug)
    {
        $this->authorize('create', Elearning::class);

        $data = $request->validate([
            'ustadz_kelas_id' => ['nullable', 'integer', 'exists:ustadz_kelas,id'],
            'subject_id'      => ['nullable', 'integer', 'exists:subjects,id'],
            'kelas_id'        => ['nullable', 'integer', 'exists:kelas,id'],
            'judul'           => ['required', 'string', 'max:255'],
            'deskripsi'       => ['nullable', 'string', 'max:1000'],
            'file'            => ['nullable', 'file', 'max:10240'],
            'link'            => ['nullable', 'url', 'max:500'],
        ]);

        $user        = auth()->user();
        $ustadzKelas = $this->elearningService->resolveUstadzKelas(
            $user,
            (int) ($data['kelas_id'] ?? 0),
            (int) ($data['subject_id'] ?? 0),
            $programSlug,
            $data['ustadz_kelas_id'] ?? null
        );

        $this->elearningService->store($data, $programSlug, $request->file('file'), $ustadzKelas);

        return redirect(tenant_route('dashboard.akademik.elearning.index', ['programSlug' => $programSlug]))
            ->with('success', 'Materi e-learning berhasil ditambahkan.');
    }

    /**
     * Delete material via ElearningService.
     */
    public function destroy(string $programSlug, int $id)
    {
        $material = Elearning::findOrFail($id);

        $this->authorize('delete', $material);

        $this->elearningService->delete($material);

        return redirect(tenant_route('dashboard.akademik.elearning.index', ['programSlug' => $programSlug]))
            ->with('success', 'Materi berhasil dihapus.');
    }
}
