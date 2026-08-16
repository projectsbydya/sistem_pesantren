<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Program;
use App\Models\Raport;
use App\Models\RaportHafalan;
use App\Models\Santri;
use App\Models\SantriProgram;
use App\Models\Schedule;
use App\Models\Subject;
use App\Policies\RaportPolicy;
use App\Services\RaportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class RaportController extends Controller
{
    private RaportService $raportService;

    public function __construct(RaportService $raportService)
    {
        $this->raportService = $raportService;
    }

    /**
     * List raport for a program
     */
    public function index(Request $request, string $programSlug)
    {
        $this->authorize('viewAny', Raport::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();

        $semester = $request->get('semester', 'ganjil');
        $tahunAjaran = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());
        $kelasId = $request->get('kelas_id');

        $accessibleSantriIds = RaportPolicy::accessibleSantriIds(auth()->user(), $program->id);

        $raportQuery = Raport::with(['santri', 'kelas'])
            ->where('program_id', $program->id)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->when($kelasId, function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            });

        if ($accessibleSantriIds !== null) {
            $raportQuery->whereIn('santri_id', $accessibleSantriIds);
        }

        $raport = $raportQuery->orderBy('created_at', 'desc')->paginate(20);

        $kelasList = Kelas::where('program_id', $program->id)
            ->orderBy('name')
            ->get();

        return view('dashboard.raport.index', compact(
            'raport', 'programSlug', 'program', 'kelasList', 'semester', 'tahunAjaran', 'kelasId'
        ));
    }

    /**
     * Show single raport detail
     */
    public function show(string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::with([
            'santri',
            'kelas',
            'nilaiRaport.subject',
            'nilaiHafalan'
        ])->where('program_id', $program->id)->findOrFail($id);

        $this->authorize('view', $raport);

        return view('dashboard.raport.show', compact('raport', 'programSlug', 'program'));
    }

    /**
     * Create form - select santri and semester
     */
    public function create(Request $request, string $programSlug)
    {
        $this->authorize('create', Raport::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();

        $kelasList = Kelas::where('program_id', $program->id)
            ->orderBy('name')
            ->get();

        $enrolledSantriByKelas = SantriProgram::where('program_id', $program->id)
            ->where('status', 'aktif')
            ->whereHas('santri', fn ($q) => $q->where('status', 'active'))
            ->with('santri')
            ->get()
            ->groupBy('kelas_id')
            ->map(fn ($items) => $items->pluck('santri')->unique('id')->sortBy('name')->values());

        foreach ($kelasList as $kelas) {
            $kelas->setRelation('santri', $enrolledSantriByKelas->get($kelas->id, collect()));
        }

        $semester = $request->get('semester', 'ganjil');
        $tahunAjaran = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());

        return view('dashboard.raport.create', compact(
            'programSlug', 'program', 'kelasList', 'semester', 'tahunAjaran'
        ));
    }

    /**
     * Generate raport for selected santri
     */
    public function generate(Request $request, string $programSlug)
    {
        $this->authorize('create', Raport::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();

        $data = $request->validate([
            'santri_id'     => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($program, $request) {
                    $enrolled = SantriProgram::where('tenant_id', tenant_id())
                        ->where('program_id', $program->id)
                        ->where('kelas_id', $request->input('kelas_id'))
                        ->where('santri_id', $value)
                        ->where('status', 'aktif')
                        ->whereHas('santri', fn ($q) => $q->where('status', 'active'))
                        ->exists();

                    if (! $enrolled) {
                        $fail('Santri tidak terdaftar aktif di kelas/program ini.');
                    }
                },
                Rule::unique('raport', 'santri_id')->where(function ($query) use ($program) {
                    return $query->where('tenant_id', tenant_id())
                        ->where('program_id', $program->id)
                        ->where('semester', request('semester'))
                        ->where('tahun_ajaran', request('tahun_ajaran'));
                }),
            ],
            'kelas_id'      => ['required', 'integer', Rule::exists('kelas', 'id')->where('tenant_id', tenant_id())->where('program_id', $program->id)],
            'semester'      => ['required', 'in:ganjil,genap'],
            'tahun_ajaran'  => ['required', 'string'],
            'total_hari_efektif' => ['required', 'integer', 'min:1'],
        ], [
            'santri_id.unique' => 'Raport untuk santri ini pada semester tersebut sudah ada.',
        ]);

        $santri = Santri::findOrFail($data['santri_id']);

        $this->authorize('generate-raport-for', [$santri, $program]);

        // Get all subjects for this program
        $subjects = Subject::where('program_id', $program->id)->get();

        // Calculate date range for the semester
        $dateRange = $this->getSemesterDateRange($data['semester'], $data['tahun_ajaran']);

        $this->raportService->generate($program, $santri, $subjects, $dateRange, [
            'kelas_id'           => $data['kelas_id'],
            'semester'           => $data['semester'],
            'tahun_ajaran'       => $data['tahun_ajaran'],
            'total_hari_efektif' => $data['total_hari_efektif'],
        ]);

        return redirect(tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]))
            ->with('success', 'Raport berhasil dibuat.');
    }

    /**
     * Edit raport nilai form
     */
    public function edit(string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::with(['nilaiRaport.subject', 'nilaiRaport.nilaiComponents', 'nilaiHafalan', 'santri', 'kelas'])
            ->where('program_id', $program->id)
            ->findOrFail($id);

        $this->authorize('update', $raport);

        return view('dashboard.raport.edit', compact('raport', 'programSlug', 'program'));
    }

    /**
     * Update raport nilai
     */
    public function update(Request $request, string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::where('program_id', $program->id)->findOrFail($id);
        $this->authorize('update', $raport);

        $data = $request->validate([
            'nilai'                 => ['required', 'array'],
            'nilai.*.deskripsi'     => ['nullable', 'string'],
            'catatan_umum'          => ['nullable', 'string'],
            'kepala_pesantren'      => ['nullable', 'string'],
            'nip_kepala'            => ['nullable', 'string'],
            'sakit'                 => ['nullable', 'integer', 'min:0'],
            'izin'                  => ['nullable', 'integer', 'min:0'],
            'alpa'                  => ['nullable', 'integer', 'min:0'],
        ]);

        $this->raportService->update($raport, $data['nilai'], [
            'catatan_umum'     => $data['catatan_umum'] ?? null,
            'kepala_pesantren' => $data['kepala_pesantren'] ?? null,
            'nip_kepala'       => $data['nip_kepala'] ?? null,
            'sakit'            => $data['sakit'] ?? null,
            'izin'             => $data['izin'] ?? null,
            'alpa'             => $data['alpa'] ?? null,
        ]);

        return redirect(tenant_route('dashboard.akademik.raport.show', ['programSlug' => $programSlug, 'id' => $id]))
            ->with('success', 'Raport berhasil diperbarui.');
    }

    /**
     * Publish raport
     */
    public function publish(string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::where('program_id', $program->id)->findOrFail($id);
        $this->authorize('publish', $raport);

        try {
            $this->raportService->publish($raport);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Raport berhasil diterbitkan.');
    }

    /**
     * Unpublish raport, returning it to draft for corrections.
     */
    public function unpublish(string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::where('program_id', $program->id)->findOrFail($id);
        $this->authorize('unpublish', $raport);

        try {
            $this->raportService->unpublish($raport);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Raport berhasil dibatalkan penerbitannya.');
    }

    /**
     * Regenerate a draft raport snapshot from the current nilai records.
     */
    public function regenerate(Request $request, string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::where('program_id', $program->id)->findOrFail($id);
        $this->authorize('regenerate', $raport);

        try {
            $this->raportService->regenerate($raport);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Raport berhasil dihasilkan ulang dari data nilai.');
    }

    /**
     * Print raport view
     */
    public function print(string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::with([
            'santri',
            'kelas',
            'nilaiRaport.subject',
            'nilaiRaport.nilaiComponents',
            'nilaiHafalan'
        ])->where('program_id', $program->id)->findOrFail($id);

        $this->authorize('view', $raport);

        return view('dashboard.raport.print', compact('raport', 'programSlug', 'program'));
    }

    /**
     * Delete raport
     */
    public function destroy(string $programSlug, int $id)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();

        $raport = Raport::where('program_id', $program->id)->findOrFail($id);
        $this->authorize('delete', $raport);

        $raport->delete();

        return redirect(tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]))
            ->with('success', 'Raport berhasil dihapus.');
    }

    /**
     * Helper: Get current academic year
     */
    private function getCurrentTahunAjaran(): string
    {
        $year = now()->year;
        $month = now()->month;

        // Academic year typically starts in July
        if ($month >= 7) {
            return "{$year}/" . ($year + 1);
        }
        return ($year - 1) . "/{$year}";
    }

    /**
     * Helper: Get semester date range
     */
    private function getSemesterDateRange(string $semester, string $tahunAjaran): array
    {
        [$startYear, $endYear] = explode('/', $tahunAjaran);

        if ($semester === 'ganjil') {
            // July - December
            return [
                'start' => "{$startYear}-07-01",
                'end' => "{$startYear}-12-31",
            ];
        }
        // Genap: January - June
        return [
            'start' => "{$endYear}-01-01",
            'end' => "{$endYear}-06-30",
        ];
    }
}
