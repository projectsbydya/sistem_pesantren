<?php

namespace App\Http\Controllers\Dashboard\Modern;

use App\Http\Controllers\Controller;
use App\Models\Muhadatsah;
use App\Models\Muhadhoroh;
use App\Models\PlacementTest;
use App\Models\PlacementTestResult;
use App\Models\UstadzKelas;
use App\Models\Vocabulary;
use App\Models\Santri;
use App\Services\ModernService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModernController extends Controller
{
    public function __construct(private ModernService $modernService) {}

    // =========================================================================
    // Feature registry
    // =========================================================================

    private function features(): array
    {
        return [
            // =========================================================================
            // Vocabulary — Unified Entity (arabic|english via ?type= query param)
            // Types read via Vocabulary::getTypes() — model method is the sole access point.
            // Add future Modern features (placement-test, language-assessment…) below.
            // =========================================================================
            'vocabulary' => [
                'model'          => Vocabulary::class,
                'getRecords'     => 'getVocabularyRecords',
                'getForSantri'   => 'getVocabularyForSantri',
                'store'          => 'storeVocabulary',
                'update'         => 'updateVocabulary',
                'successMessage' => 'Kosakata berhasil disimpan.',
                'typeModel'      => Vocabulary::class,
                'rules'          => fn (?\App\Models\Program $program = null) => [
                    'word'             => ['required', 'string', 'max:255'],
                    'language'         => ['required', Rule::in(Vocabulary::LANGUAGES)],
                    'translation'      => ['nullable', 'string', 'max:255'],
                    'example_sentence' => ['nullable', 'string'],
                    'category'         => ['nullable', 'string', 'max:100'],
                    'score'            => ['nullable', 'integer', 'min:0', 'max:100'],
                    'status'           => ['required', Rule::in(Vocabulary::getStatuses())],
                ],
            ],
            // =========================================================================
            // Muhadatsah — Unified Entity (arabic|english via ?type= query param)
            // Types read via Muhadatsah::getTypes() — model method is the sole access point.
            // =========================================================================
            'muhadatsah' => [
                'model'          => Muhadatsah::class,
                'getRecords'     => 'getMuhadatsahRecords',
                'getForSantri'   => 'getMuhadatsahForSantri',
                'store'          => 'storeMuhadatsah',
                'update'         => 'updateMuhadatsah',
                'successMessage' => 'Data muhadatsah berhasil disimpan.',
                'typeModel'      => Muhadatsah::class,
                'rules'          => fn (?\App\Models\Program $program = null) => [
                    'topic'    => ['required', 'string', 'max:255'],
                    'content'  => ['nullable', 'string'],
                    'category' => ['nullable', 'string', 'max:100'],
                    'score'    => ['nullable', 'integer', 'min:0', 'max:100'],
                ],
            ],
            // =========================================================================
            // Muhadhoroh — Unified Entity (muhadhoroh|public-speaking via ?type= query param)
            // Types read via Muhadhoroh::getTypes() — model method is the sole access point.
            // =========================================================================
            'muhadhoroh' => [
                'model'          => Muhadhoroh::class,
                'getRecords'     => 'getMuhadhorohRecords',
                'getForSantri'   => 'getMuhadhorohForSantri',
                'store'          => 'storeMuhadhoroh',
                'update'         => 'updateMuhadhoroh',
                'successMessage' => 'Data muhadhoroh berhasil disimpan.',
                'typeModel'      => Muhadhoroh::class,
                'rules'          => fn (?
\App\Models\Program $program = null) => [
                    'title'              => ['required', 'string', 'max:255'],
                    'theme_id'           => ['nullable', 'integer', Rule::exists('muhadhoroh_themes', 'id')
                        ->where('tenant_id', tenant_id())
                        ->where('program_id', $program->id)],

                    'language'           => ['nullable', 'string', 'max:10'],
                    'description'        => ['nullable', 'string'],
                    'score'              => ['nullable', 'integer', 'min:0', 'max:100'],
                    'performed_at'       => ['nullable', 'date'],
                    'is_video_submission' => ['nullable', 'boolean'],
                    'submission_url'     => ['nullable', 'url', 'max:500'],
                ],
            ],
            // =========================================================================
            // Placement Test — Test header with per-santri results (one test → many results)
            // Types read via PlacementTest::getTypes() — model method is the sole access point.
            // =========================================================================
            'placement-test' => [
                'model'          => PlacementTest::class,
                'getRecords'     => 'getPlacementTestRecords',
                'getForSantri'   => 'getPlacementTestResultsForSantri',
                'store'          => 'storePlacementTest',
                'update'         => 'updatePlacementTest',
                'successMessage' => 'Placement test berhasil disimpan.',
                'typeModel'      => PlacementTest::class,
                'rules'          => fn (?
\App\Models\Program $program = null) => [
                    'title'       => ['required', 'string', 'max:255'],
                    'date'        => ['nullable', 'date'],
                    'description' => ['nullable', 'string'],
                    'max_score' => ['nullable', 'integer', 'min:0', 'max:100'],
                ],
            ],
        ];
    }

    private function feature(string $featureSlug): array
    {
        return $this->features()[$featureSlug] ?? abort(404);
    }

    private function commonRules(string $featureSlug): array
    {
        if ($featureSlug === 'placement-test') {
            return [];
        }

        return [
            'santri_id'       => ['required', 'integer', 'exists:santri,id'],
            'ustadz_kelas_id' => ['nullable', 'integer', 'exists:ustadz_kelas,id'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    // Resolve ?type= for features that use type-based sub-categorisation.
    // Reads allowed types from the model's TYPES constant — model is the sole source of truth.
    private function resolveType(Request $request, array $feat): ?string
    {
        if (empty($feat['typeModel'])) {
            return null;
        }
        $types = $feat['typeModel']::getTypes();
        $type  = $request->query('type', $types[0]);
        abort_unless(in_array($type, $types, true), 404);
        return $type;
    }

    // =========================================================================
    // Generic CRUD actions — shared across all Modern features
    // =========================================================================

    public function index(Request $request, string $programSlug, string $featureSlug): View
    {
        $feat  = $this->feature($featureSlug);
        $model = $feat['model'];
        $this->authorize('viewAny', $model);

        $type            = $this->resolveType($request, $feat);
        $program         = $this->modernService->resolveProgram($programSlug);
        $kelasId         = $request->filled('kelas_id') ? (int) $request->input('kelas_id') : null;
        $records         = $this->modernService->{$feat['getRecords']}($program, $type, $kelasId);
        $santriList      = $this->modernService->getSantriListForProgram($program);
        $kelasList       = $this->modernService->getKelasListForProgram($program);
        $ustadzKelasList = UstadzKelas::where('program_id', $program->id)->with('kelas')->get();
        $viewMeta        = ['type' => $type, 'typeSource' => isset($feat['typeModel']) ? $feat['typeModel']::getTypes() : null];
        $themeList       = $featureSlug === 'muhadhoroh' ? $this->modernService->getMuhadhorohThemes($program) : collect();
        $levelList       = $featureSlug === 'placement-test' ? $this->modernService->getPlacementTestLevels($program) : collect();

        return view("dashboard.modern.{$featureSlug}.index", compact(
            'program', 'programSlug', 'featureSlug', 'type', 'records', 'santriList', 'kelasList', 'kelasId', 'ustadzKelasList', 'viewMeta', 'themeList', 'levelList'
        ));
    }

    public function store(Request $request, string $programSlug, string $featureSlug): RedirectResponse
    {
        $feat    = $this->feature($featureSlug);
        $model   = $feat['model'];
        $type    = $this->resolveType($request, $feat);
        $program = $this->modernService->resolveProgram($programSlug);

        $data = $request->validate(array_merge($this->commonRules($featureSlug), ($feat['rules'])($program)));

        if (!empty($data['ustadz_kelas_id'])) {
            $ustadzKelas = UstadzKelas::findOrFail($data['ustadz_kelas_id']);
            $this->authorize('recordFor', [$model, $ustadzKelas]);
        } else {
            $this->authorize('create', $model);
        }

        if ($type !== null) {
            $data['type'] = $type;
        }
        $data['program_id'] = $program->id;
        $this->modernService->{$feat['store']}($data);

        $redirect = tenant_route("dashboard.modern.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $type,
        ]));

        return redirect($redirect)->with('success', $feat['successMessage']);
    }

    public function show(Request $request, string $programSlug, int $santriId): View
    {
        // NOTE: $featureSlug is injected via ->defaults() on the route, not a
        // real URI segment. Laravel appends route defaults to the parameter
        // list *after* real URI parameters, so it must NOT be type-hinted
        // positionally alongside another URI scalar (like $santriId) or the
        // values get silently swapped. Pull it from the route explicitly.
        $featureSlug = $request->route('featureSlug');
        $feat  = $this->feature($featureSlug);
        $model = $feat['model'];
        $this->authorize('viewAny', $model);

        $type    = $this->resolveType($request, $feat);
        $program = $this->modernService->resolveProgram($programSlug);
        $santri  = Santri::findOrFail($santriId);
        $this->authorize('view', $santri);

        $records  = $this->modernService->{$feat['getForSantri']}($santriId, $program, $type);
        $viewMeta = ['type' => $type, 'typeSource' => isset($feat['typeModel']) ? $feat['typeModel']::getTypes() : null];

        return view("dashboard.modern.{$featureSlug}.show", compact(
            'program', 'programSlug', 'featureSlug', 'type', 'santri', 'records', 'viewMeta'
        ));
    }

    public function edit(Request $request, string $programSlug, int $id): View
    {
        // See note in show(): $featureSlug is a route default, not a real
        // URI parameter, so it must be read from the route rather than
        // type-hinted positionally next to $id.
        $featureSlug = $request->route('featureSlug');
        $feat   = $this->feature($featureSlug);
        $model  = $feat['model'];
        $record = $model::findOrFail($id);
        $this->authorize('update', $record);

        $program    = $this->modernService->resolveProgram($programSlug);
        $santriList = $this->modernService->getSantriListForProgram($program);
        $viewMeta   = ['type' => $record->type ?? null, 'typeSource' => isset($feat['typeModel']) ? $feat['typeModel']::getTypes() : null];
        $themeList  = $featureSlug === 'muhadhoroh' ? $this->modernService->getMuhadhorohThemes($program) : collect();
        $levelList  = $featureSlug === 'placement-test' ? $this->modernService->getPlacementTestLevels($program) : collect();

        return view("dashboard.modern.{$featureSlug}.edit", compact(
            'program', 'programSlug', 'featureSlug', 'record', 'santriList', 'viewMeta', 'themeList', 'levelList'
        ));
    }

    public function update(Request $request, string $programSlug, int $id): RedirectResponse
    {
        // See note in show(): $featureSlug is a route default, not a real
        // URI parameter, so it must be read from the route rather than
        // type-hinted positionally next to $id.
        $featureSlug = $request->route('featureSlug');
        $feat   = $this->feature($featureSlug);
        $model  = $feat['model'];
        $record = $model::findOrFail($id);
        $this->authorize('update', $record);

        $program = $this->modernService->resolveProgram($programSlug);
        $data = $request->validate(array_merge($this->commonRules($featureSlug), ($feat['rules'])($program)));
        $this->modernService->{$feat['update']}($record, $data);

        $redirect = tenant_route("dashboard.modern.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $record->type ?? null,
        ]));

        return redirect($redirect)->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, string $programSlug, int $id): RedirectResponse
    {
        // See note in show(): $featureSlug is a route default, not a real
        // URI parameter, so it must be read from the route rather than
        // type-hinted positionally next to $id.
        $featureSlug = $request->route('featureSlug');
        $feat   = $this->feature($featureSlug);
        $model  = $feat['model'];
        $record = $model::findOrFail($id);
        $this->authorize('delete', $record);

        $type = $record->type ?? null;
        $record->delete();

        $redirect = tenant_route("dashboard.modern.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $type,
        ]));

        return redirect($redirect)->with('success', 'Data berhasil dihapus.');
    }

    // =========================================================================
    // Placement Test Result CRUD — dedicated routes, separate from test header
    // =========================================================================

    public function storeResult(Request $request, string $programSlug, string $featureSlug): RedirectResponse
    {
        $program = $this->modernService->resolveProgram($programSlug);
        $type    = $this->resolveType($request, $this->feature($featureSlug));

        $this->authorize('create', [PlacementTestResult::class, $program]);

        $data = $request->validate([
            'placement_test_id' => ['required', 'integer', Rule::exists('placement_tests', 'id')->where('tenant_id', tenant_id())->where('program_id', $program->id)],
            'santri_id'         => ['required', 'integer', Rule::exists('santri', 'id')->where('tenant_id', tenant_id())],
            'score'             => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        $data['program_id'] = $program->id;
        $this->modernService->storePlacementTestResult($data);

        $redirect = tenant_route("dashboard.modern.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $type,
        ]));

        return redirect($redirect)->with('success', 'Hasil placement test berhasil disimpan.');
    }

    public function updateResult(Request $request, string $programSlug, int $id): RedirectResponse
    {
        // See note in show(): $featureSlug is a route default, not a real
        // URI parameter, so it must be read from the route rather than
        // type-hinted positionally next to $id.
        $featureSlug = $request->route('featureSlug');
        $program = $this->modernService->resolveProgram($programSlug);
        $type    = $this->resolveType($request, $this->feature($featureSlug));

        $result = PlacementTestResult::findOrFail($id);
        $this->authorize('update', [$result, $program]);

        $data = $request->validate([
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->modernService->updatePlacementTestResult($result, $data);

        $redirect = tenant_route("dashboard.modern.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $type,
        ]));

        return redirect($redirect)->with('success', 'Hasil placement test berhasil diperbarui.');
    }

    public function destroyResult(Request $request, string $programSlug, int $id): RedirectResponse
    {
        // See note in show(): $featureSlug is a route default, not a real
        // URI parameter, so it must be read from the route rather than
        // type-hinted positionally next to $id.
        $featureSlug = $request->route('featureSlug');
        $program = $this->modernService->resolveProgram($programSlug);
        $type    = $this->resolveType($request, $this->feature($featureSlug));

        $result = PlacementTestResult::findOrFail($id);
        $this->authorize('delete', [$result, $program]);

        $this->modernService->deletePlacementTestResult($result);

        $redirect = tenant_route("dashboard.modern.{$featureSlug}.index", array_filter([
            'programSlug' => $programSlug,
            'type'        => $type,
        ]));

        return redirect($redirect)->with('success', 'Hasil placement test berhasil dihapus.');
    }
}
