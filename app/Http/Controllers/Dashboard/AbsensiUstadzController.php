<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\AbsensiUstadzRequest;
use App\Models\AbsensiUstadz;
use App\Services\SDM\AbsensiUstadzService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsensiUstadzController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private AbsensiUstadzService $absensiUstadzService
    ) {}

    /**
     * Step 1: Ustadz list for a chosen date — filtered by user relations.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AbsensiUstadz::class);

        $request->merge([
            'schedule_id' => $request->input('schedule_id', $request->input('jadwal_id')),
        ]);
        $data = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'schedule_id' => [
                'nullable',
                'integer',
                Rule::exists('jadwal', 'id')->where('tenant_id', tenant_id()),
            ],
        ]);

        $tanggal = $data['tanggal'] ?? today()->toDateString();
        $jadwalList = $this->absensiUstadzService->getEligibleSchedulesForDate($tanggal);
        $jadwalId = isset($data['schedule_id']) ? (int) $data['schedule_id'] : null;
        $jadwal = null;
        $ustadzList = collect();
        $existing = collect();

        if ($jadwalId !== null) {
            $attendance = $this->absensiUstadzService->findAttendance($jadwalId, $tanggal);

            if ($attendance !== null) {
                $this->authorize('update', $attendance);
                $jadwal = $attendance->schedule;
                $ustadzList = collect([$this->absensiUstadzService->resolveTeacherForAttendance($jadwalId, $tanggal, $attendance)]);
                $existing = collect([$attendance->ustadz_id => $attendance]);
            } else {
                $jadwal = $this->absensiUstadzService->resolveSchedule($jadwalId, $tanggal);
                $this->authorize('createFor', [AbsensiUstadz::class, $jadwal]);
                $ustadzList = collect([$this->absensiUstadzService->resolveTeacherForAttendance($jadwalId, $tanggal)]);
            }
        }

        return view('dashboard.absensi.ustadz.index', compact(
            'jadwalList', 'jadwal', 'jadwalId', 'ustadzList', 'tanggal', 'existing'
        ));
    }

    /**
     * Store / update single ustadz attendance.
     */
    public function store(AbsensiUstadzRequest $request)
    {
        $tanggal = $request->tanggal;
        $jadwalId = (int) $request->schedule_id;
        $attendance = $this->absensiUstadzService->findAttendance($jadwalId, $tanggal);

        if ($attendance !== null) {
            $this->authorize('update', $attendance);
        } else {
            $jadwal = $this->absensiUstadzService->resolveSchedule($jadwalId, $tanggal);
            $this->authorize('createFor', [AbsensiUstadz::class, $jadwal]);
        }

        $this->absensiUstadzService->saveAttendance(
            $jadwalId,
            $tanggal,
            $request->status,
            $request->catatan
        );

        return redirect(tenant_route('dashboard.sdm.absensi-ustadz.index', [
            'tanggal' => $tanggal,
            'jadwal_id' => $jadwalId,
        ]))
            ->with('success', 'Absensi ustadz berhasil disimpan.');
    }

    /**
     * Bulk store: submit all ustadz attendance for a date in one POST.
     */
    public function storeBulk(Request $request)
    {
        $this->authorize('viewAny', AbsensiUstadz::class);

        $request->merge([
            'schedule_id' => $request->input('schedule_id', $request->input('jadwal_id')),
        ]);
        $data = $request->validate([
            'schedule_id'         => [
                'required',
                'integer',
                Rule::exists('jadwal', 'id')->where('tenant_id', tenant_id()),
            ],
            'tanggal'             => ['required', 'date'],
            'absensi'             => ['required', 'array', 'size:1'],
            'absensi.*.status'    => ['required', Rule::in(AbsensiUstadz::STATUS)],
            'absensi.*.catatan'   => ['nullable', 'string', 'max:500'],
        ]);

        $tanggal = $data['tanggal'];
        $jadwalId = (int) $data['schedule_id'];
        $attendance = $this->absensiUstadzService->findAttendance($jadwalId, $tanggal);

        if ($attendance !== null) {
            $this->authorize('update', $attendance);
        } else {
            $jadwal = $this->absensiUstadzService->resolveSchedule($jadwalId, $tanggal);
            $this->authorize('createFor', [AbsensiUstadz::class, $jadwal]);
        }

        $this->absensiUstadzService->saveBulkAttendance($data['absensi'], $jadwalId, $tanggal);

        return redirect(tenant_route('dashboard.sdm.absensi-ustadz.index', [
            'tanggal' => $tanggal,
            'jadwal_id' => $jadwalId,
        ]))
            ->with('success', 'Absensi ustadz berhasil disimpan.');
    }

    /**
     * Rekap: show absensi for all ustadz on a date.
     */
    public function rekap(Request $request)
    {
        $this->authorize('viewAny', AbsensiUstadz::class);

        $request->validate(['tanggal' => ['required', 'date']]);

        $tanggal = $request->tanggal;
        $absensiList = $this->absensiUstadzService->getAttendanceRecap($tanggal);
        $summary = $this->absensiUstadzService->getAttendanceSummary($tanggal);

        return view('dashboard.absensi.ustadz.rekap', compact('absensiList', 'tanggal', 'summary'));
    }
}
