<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\BulkAbsensiRequest;
use App\Models\AbsensiSantri;
use App\Models\Program;
use App\Models\Schedule;
use App\Services\AbsensiService;
use App\Services\FeatureDependencyService;
use App\Services\ProgramValidationService;
use App\Services\TenantService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AbsensiSantriController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private AbsensiService $absensiService
    ) {}

    /**
     * Step 1: Show jadwal list for a chosen date.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AbsensiSantri::class);

        // Check dependencies - show warning if missing
        $dependencyCheck = FeatureDependencyService::validateInputAbsensi();
        if (!$dependencyCheck['can_access']) {
            return view('dashboard.absensi.santri.index', [
                'warning' => $dependencyCheck,
                'jadwalList' => collect(),
                'tanggal' => $request->get('tanggal', today()->toDateString()),
                'hariIndo' => null,
                'type' => $request->route('programSlug'),
                'programSlug' => $request->route('programSlug'),
            ]);
        }

        $tenant = TenantService::getTenant();

        // Resolve program through tenant-program relation, no hardcoded fallback
        $resolved = ProgramValidationService::resolveProgram($request->route('programSlug'), $tenant);

        if (!$resolved) {
            throw new NotFoundHttpException('Program tidak ditemukan untuk tenant ini.');
        }

        $program = $resolved['program'];
        $type = $resolved['slug'];
        $programSlug = $type;

        $tanggal = $request->get('tanggal', today()->toDateString());
        $carbonDate = \Carbon\Carbon::parse($tanggal);
        $hariIndo = Schedule::HARI[$carbonDate->dayOfWeekIso - 1] ?? null;

        $jadwalList = $this->absensiService->getSchedulesForAttendance($type, $tanggal);

        return view('dashboard.absensi.santri.index', compact('jadwalList', 'tanggal', 'hariIndo', 'type', 'programSlug'));
    }

    /**
     * Step 2: Santri list for a jadwal + date.
     */
    public function input(Request $request)
    {
        $request->validate([
            'jadwal_id' => ['required', 'integer', 'exists:jadwal,id'],
            'tanggal'   => ['required', 'date'],
        ]);

        $tenant = TenantService::getTenant();

        // Validate program belongs to tenant
        $resolved = ProgramValidationService::resolveProgram($request->route('programSlug'), $tenant);

        if (!$resolved) {
            throw new NotFoundHttpException('Program tidak ditemukan untuk tenant ini.');
        }

        $program = $resolved['program'];
        $type = $resolved['slug'];
        $programSlug = $type;

        $jadwal = $this->absensiService->getScheduleForAttendance((int) $request->jadwal_id);
        $tanggal = $request->tanggal;

        $this->authorize('recordFor', $jadwal);

        $santriList = $this->absensiService->getSantriList((int) $jadwal->kelas_id);
        $existing = $this->absensiService->getExistingAttendance($jadwal->id, $tanggal, $type);

        return view('dashboard.absensi.santri.input', compact('jadwal', 'tanggal', 'santriList', 'existing', 'type', 'programSlug'));
    }

    /**
     * Step 3: Bulk store/update.
     */
    public function store(BulkAbsensiRequest $request)
    {
        $tenant = TenantService::getTenant();

        // Validate program belongs to tenant
        $resolved = ProgramValidationService::resolveProgram($request->route('programSlug'), $tenant);

        if (!$resolved) {
            throw new NotFoundHttpException('Program tidak ditemukan untuk tenant ini.');
        }

        $type = $resolved['slug'];

        $jadwal = $this->absensiService->getScheduleForAttendance((int) $request->jadwal_id);
        $tanggal = $request->tanggal;

        $this->authorize('recordFor', $jadwal);
        $this->absensiService->storeBulkAttendance($request->absensi, $jadwal->id, $tanggal, $type);

        $rekapRoute = 'dashboard.akademik.absensi.rekap';
        return redirect(tenant_route($rekapRoute, ['programSlug' => $type,
            'jadwal_id' => $jadwal->id,
            'tanggal'   => $tanggal,
        ]))->with('success', 'Absensi santri berhasil disimpan.');
    }

    /**
     * Rekap: saved attendance for jadwal + date.
     */
    public function rekap(Request $request)
    {
        $request->validate([
            'jadwal_id' => ['required', 'integer', 'exists:jadwal,id'],
            'tanggal'   => ['required', 'date'],
        ]);

        $tenant = TenantService::getTenant();

        // Validate program belongs to tenant
        $resolved = ProgramValidationService::resolveProgram($request->route('programSlug'), $tenant);

        if (!$resolved) {
            throw new NotFoundHttpException('Program tidak ditemukan untuk tenant ini.');
        }

        $program = $resolved['program'];
        $type = $resolved['slug'];
        $programSlug = $type;

        $jadwal = $this->absensiService->getScheduleForAttendance((int) $request->jadwal_id);
        $tanggal = $request->tanggal;

        $this->authorize('recordFor', $jadwal);

        $absensiList = $this->absensiService->getAttendanceRecap($jadwal->id, $tanggal, $type);
        $summary = $absensiList->groupBy('status')->map->count();

        return view('dashboard.absensi.santri.rekap', compact('jadwal', 'tanggal', 'absensiList', 'summary', 'type', 'programSlug'));
    }
}
