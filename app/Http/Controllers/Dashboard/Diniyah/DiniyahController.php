<?php

namespace App\Http\Controllers\Dashboard\Diniyah;

use App\Http\Controllers\Controller;
use App\Models\DiniyahAssessment;
use App\Models\DiniyahHafalan;
use App\Models\DiniyahMonitoring;
use App\Models\Santri;
use App\Models\UstadzKelas;
use App\Services\DiniyahService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiniyahController extends Controller
{
    public function __construct(private DiniyahService $diniyahService) {}

    // =========================================================================
    // Feature registry
    // =========================================================================

    private function features(): array
    {
        return [
            // =========================================================================
            // Diniyah Hafalan — Unified Entity (replaces separate Doa/Hadits/Surat)
            // ARCHITECTURE FROZEN: All hafalan types use DiniyahHafalan with type column
            // =========================================================================
            'hafalan-doa' => [
                'model'          => DiniyahHafalan::class,
                'getRecords'     => 'getHafalanDoaRecords',
                'getForSantri'   => 'getHafalanDoaProgress',
                'store'          => 'storeHafalan',
                'successMessage' => 'Hafalan Doa berhasil disimpan.',
                'type'           => 'doa',
                'viewName'       => 'dashboard.diniyah.hafalan',
                'icon'           => 'fa-hands-praying',
                'placeholders'   => [
                    'title'       => 'Doa sebelum makan...',
                    'target'      => 'Hafal doa sebelum & sesudah makan',
                    'achievement' => 'Sudah hafal doa sebelum makan...',
                ],
                'rules'          => fn () => [
                    'title'       => ['required', 'string', 'max:255'],
                    'target'      => ['nullable', 'string'],
                    'achievement' => ['nullable', 'string'],
                    'status'      => ['required', Rule::in(DiniyahHafalan::STATUS)],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'doa',
            ],
            'hafalan-hadits' => [
                'model'          => DiniyahHafalan::class,
                'getRecords'     => 'getHafalanHaditsRecords',
                'getForSantri'   => 'getHafalanHaditsProgress',
                'store'          => 'storeHafalan',
                'successMessage' => 'Hafalan Hadits berhasil disimpan.',
                'type'           => 'hadits',
                'viewName'       => 'dashboard.diniyah.hafalan',
                'icon'           => 'fa-book-open',
                'placeholders'   => [
                    'title'       => 'Hadits tentang niat...',
                    'target'      => 'HR. Bukhari no. 1',
                    'achievement' => 'Sudah hafal matan...',
                ],
                'rules'          => fn () => [
                    'title'       => ['required', 'string', 'max:255'],
                    'target'      => ['nullable', 'string'],
                    'achievement' => ['nullable', 'string'],
                    'status'      => ['required', Rule::in(DiniyahHafalan::STATUS)],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'hadits',
            ],
            'hafalan-surat' => [
                'model'          => DiniyahHafalan::class,
                'getRecords'     => 'getHafalanSuratRecords',
                'getForSantri'   => 'getHafalanSuratProgress',
                'store'          => 'storeHafalan',
                'successMessage' => 'Hafalan Surat Pendek berhasil disimpan.',
                'type'           => 'surat',
                'viewName'       => 'dashboard.diniyah.hafalan',
                'icon'           => 'fa-book-quran',
                'placeholders'   => [
                    'title'       => 'Al-Fatihah...',
                    'target'      => 'Ayat 1-7',
                    'achievement' => 'Sudah hafal 3 ayat...',
                ],
                'rules'          => fn () => [
                    'title'       => ['required', 'string', 'max:255'],
                    'target'      => ['nullable', 'string'],
                    'achievement' => ['nullable', 'string'],
                    'status'      => ['required', Rule::in(DiniyahHafalan::STATUS)],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'surat',
            ],
            // =========================================================================
            // Diniyah Monitoring — Unified Entity (replaces separate Sholat/Adab/Akhlak)
            // ARCHITECTURE FROZEN: All monitoring types use DiniyahMonitoring with type column
            // =========================================================================
            'monitoring-sholat' => [
                'model'          => DiniyahMonitoring::class,
                'getRecords'     => 'getMonitoringSholatRecords',
                'getForSantri'   => 'getMonitoringSholatForSantri',
                'store'          => 'storeMonitoringSholat',
                'successMessage' => 'Monitoring Sholat berhasil disimpan.',
                'type'           => 'sholat',
                'rules'          => fn () => [
                    'date'    => ['required', 'date'],
                    'aspect'  => ['required', Rule::in(DiniyahMonitoring::SHOLAT_TIMES)],
                    'status'  => ['required', Rule::in(DiniyahMonitoring::SHOLAT_STATUSES)],
                    'flag'    => ['boolean'],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'sholat',
            ],
            'monitoring-adab' => [
                'model'          => DiniyahMonitoring::class,
                'getRecords'     => 'getMonitoringAdabRecords',
                'getForSantri'   => 'getMonitoringAdabForSantri',
                'store'          => 'storeMonitoringAdab',
                'successMessage' => 'Monitoring Adab berhasil disimpan.',
                'type'           => 'adab',
                'rules'          => fn () => [
                    'date'    => ['required', 'date'],
                    'aspect'  => ['required', 'string', 'max:100'],
                    'status'  => ['required', Rule::in(DiniyahMonitoring::AKHLAK_STATUSES)],
                    'score'   => ['required', 'integer', 'min:1', 'max:4'],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'adab',
            ],
            'monitoring-akhlak' => [
                'model'          => DiniyahMonitoring::class,
                'getRecords'     => 'getMonitoringAkhlakRecords',
                'getForSantri'   => 'getMonitoringAkhlakForSantri',
                'store'          => 'storeMonitoringAkhlak',
                'successMessage' => 'Monitoring Akhlak berhasil disimpan.',
                'type'           => 'akhlak',
                'rules'          => fn () => [
                    'date'     => ['required', 'date'],
                    'aspect'   => ['required', 'string', 'max:100'],
                    'category' => ['nullable', Rule::in(array_keys(DiniyahMonitoring::AKHLAK_CATEGORIES))],
                    'status'   => ['required', Rule::in(DiniyahMonitoring::AKHLAK_STATUSES)],
                    'score'    => ['required', 'integer', 'min:1', 'max:4'],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'akhlak',
            ],
            // =========================================================================
            // Diniyah Assessment — Unified Entity (replaces separate Nilai Keagamaan/Akhlak)
            // ARCHITECTURE FROZEN: All assessment types use DiniyahAssessment with type column
            // =========================================================================
            'nilai-keagamaan' => [
                'model'          => DiniyahAssessment::class,
                'getRecords'     => 'getNilaiKeagamaanRecords',
                'getForSantri'   => 'getNilaiKeagamaanForSantri',
                'store'          => 'storeNilaiKeagamaan',
                'successMessage' => 'Nilai Keagamaan berhasil disimpan.',
                'type'           => 'keagamaan',
                'rules'          => fn () => [
                    'aspect'  => ['required', 'string', 'max:100'],
                    'score'   => ['required', 'numeric', 'min:0', 'max:100'],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'keagamaan',
            ],
            'nilai-akhlak' => [
                'model'          => DiniyahAssessment::class,
                'getRecords'     => 'getNilaiAkhlakRecords',
                'getForSantri'   => 'getNilaiAkhlakForSantri',
                'store'          => 'storeNilaiAkhlak',
                'successMessage' => 'Nilai Akhlak berhasil disimpan.',
                'type'           => 'akhlak',
                'rules'          => fn () => [
                    'aspect'  => ['required', 'string', 'max:100'],
                    'score'   => ['required', 'numeric', 'min:0', 'max:100'],
                ],
                'beforeStore'    => fn (Request $request, array &$data) => $data['type'] = 'akhlak',
            ],
        ];
    }

    private function feature(string $featureSlug): array
    {
        return $this->features()[$featureSlug]
            ?? abort(404);
    }

    private function viewName(array $feat, string $featureSlug): string
    {
        return $feat['viewName'] ?? "dashboard.diniyah.{$featureSlug}";
    }

    // =========================================================================
    // Shared common validation rules (every feature)
    // =========================================================================

    private function commonRules(): array
    {
        return [
            'santri_id'       => ['required', 'integer', 'exists:santri,id'],
            'ustadz_kelas_id' => ['nullable', 'integer', 'exists:ustadz_kelas,id'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    // =========================================================================
    // Actions
    // =========================================================================

    public function index(Request $request, string $programSlug, string $featureSlug): View
    {
        $feat    = $this->feature($featureSlug);
        $model   = $feat['model'];

        $this->authorize('viewAny', $model);

        $program         = $this->diniyahService->resolveProgram($programSlug);
        $kelasId         = $request->filled('kelas_id') ? (int) $request->input('kelas_id') : null;
        $records         = $this->diniyahService->{$feat['getRecords']}($program, $kelasId);
        $santriList      = $this->diniyahService->getSantriListForProgram($program);
        $kelasList       = $this->diniyahService->getKelasListForProgram($program);
        $ustadzKelasList = UstadzKelas::where('program_id', $program->id)->with('kelas')->get();
        $viewMeta        = [
            'type'         => $feat['type'] ?? null,
            'icon'         => $feat['icon'] ?? null,
            'placeholders' => $feat['placeholders'] ?? [],
        ];

        return view("{$this->viewName($feat, $featureSlug)}.index", compact(
            'program', 'programSlug', 'featureSlug', 'records', 'santriList', 'kelasList', 'kelasId', 'ustadzKelasList', 'viewMeta'
        ));
    }

    public function store(Request $request, string $programSlug, string $featureSlug): RedirectResponse
    {
        $feat    = $this->feature($featureSlug);
        $model   = $feat['model'];
        $program = $this->diniyahService->resolveProgram($programSlug);

        $data = $request->validate(
            array_merge($this->commonRules(), ($feat['rules'])())
        );

        if (!empty($data['ustadz_kelas_id'])) {
            $ustadzKelas = UstadzKelas::findOrFail($data['ustadz_kelas_id']);
            $this->authorize('recordFor', [$model, $ustadzKelas]);
        } else {
            $this->authorize('create', $model);
        }

        if (isset($feat['beforeStore'])) {
            ($feat['beforeStore'])($request, $data);
        }

        $data['program_id'] = $program->id;
        $this->diniyahService->{$feat['store']}($data);

        return redirect(tenant_route("dashboard.diniyah.{$featureSlug}.index", ['programSlug' => $programSlug]))
            ->with('success', $feat['successMessage']);
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

        $program = $this->diniyahService->resolveProgram($programSlug);
        $santri  = Santri::findOrFail($santriId);
        $this->authorize('view', $santri);

        $records = $this->diniyahService->{$feat['getForSantri']}($santriId, $program);
        $viewMeta = [
            'type'         => $feat['type'] ?? null,
            'icon'         => $feat['icon'] ?? null,
            'placeholders' => $feat['placeholders'] ?? [],
        ];

        return view("{$this->viewName($feat, $featureSlug)}.show", compact('program', 'programSlug', 'featureSlug', 'santri', 'records', 'viewMeta'));
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
        $record->delete();

        return redirect(tenant_route("dashboard.diniyah.{$featureSlug}.index", ['programSlug' => $programSlug]))
            ->with('success', 'Record dihapus.');
    }

    // =========================================================================
    // Diniyah Monitoring Specific Actions (Rekap & Riwayat)
    // =========================================================================

    public function rekap(Request $request, string $programSlug, string $featureSlug): View
    {
        $feat = $this->feature($featureSlug);
        $model = $feat['model'];

        $this->authorize('viewAny', $model);
        $this->authorize('rekap', $model);

        $program = $this->diniyahService->resolveProgram($programSlug);
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $type = $feat['type'] ?? null;

        if ($feat['model'] === DiniyahMonitoring::class) {
            $rekap = $this->diniyahService->getMonitoringRekap($program, $from, $to, $type);
        } else {
            $rekap = $this->diniyahService->getAssessmentRekap($program, $from, $to, $type);
        }
        $santriList = $this->diniyahService->getSantriListForProgram($program);

        return view("{$this->viewName($feat, $featureSlug)}.rekap", compact(
            'program', 'programSlug', 'featureSlug', 'rekap', 'santriList', 'from', 'to'
        ));
    }

    public function riwayat(Request $request, string $programSlug, int $santriId): View
    {
        // See note in show(): $featureSlug is a route default, not a real
        // URI parameter, so it must be read from the route rather than
        // type-hinted positionally next to $santriId.
        $featureSlug = $request->route('featureSlug');
        $feat = $this->feature($featureSlug);
        $model = $feat['model'];

        $this->authorize('viewAny', $model);
        $this->authorize('riwayat', [$model, $santriId]);

        $program = $this->diniyahService->resolveProgram($programSlug);
        $santri = Santri::findOrFail($santriId);

        $type = $feat['type'] ?? null;

        if ($feat['model'] === DiniyahMonitoring::class) {
            $records = $this->diniyahService->getMonitoringRiwayat($santriId, $program, $type);
        } else {
            $records = $this->diniyahService->getAssessmentRiwayat($santriId, $program, $type);
        }

        return view("{$this->viewName($feat, $featureSlug)}.riwayat", compact(
            'program', 'programSlug', 'featureSlug', 'santri', 'records'
        ));
    }
}
