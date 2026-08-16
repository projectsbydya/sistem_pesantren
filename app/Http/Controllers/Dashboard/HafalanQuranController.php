<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HafalanQuran;
use App\Models\Program;
use App\Models\Santri;
use App\Models\Schedule;
use App\Services\HafalanService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HafalanQuranController extends Controller
{
    public function __construct(
        private HafalanService $hafalanService
    ) {}

    /**
     * List all hafalan quran records.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', HafalanQuran::class);

        $records = $this->hafalanService->getHafalanQuranRecords();
        $santriList = $this->hafalanService->getSantriList();

        return view('dashboard.hafalan-quran.index', compact('records', 'santriList'));
    }

    /**
     * Store a new hafalan quran record.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'santri_id'      => ['required', 'integer', 'exists:santri,id'],
            'ustadz_kelas_id' => ['required', 'integer', 'exists:ustadz_kelas,id'],
            'tanggal'        => ['required', 'date'],
            'surah'          => ['required', 'string', 'max:100'],
            'ayat_dari'      => ['nullable', 'string', 'max:10'],
            'ayat_sampai'    => ['nullable', 'string', 'max:10'],
            'juz'            => ['nullable', 'integer', 'min:1', 'max:30'],
            'nilai'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status'         => ['required', Rule::in(HafalanQuran::STATUS)],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ]);

        $ustadzKelas = \App\Models\UstadzKelas::findOrFail($data['ustadz_kelas_id']);
        $this->authorize('recordFor', [HafalanQuran::class, $ustadzKelas]);

        $this->hafalanService->storeHafalanQuran($data);

        return redirect(tenant_route('dashboard.kepesantrenan.hafalan-quran.index'))
            ->with('success', 'Hafalan Quran berhasil disimpan.');
    }

    /**
     * Santri progress view.
     */
    public function show(int $santriId)
    {
        $santri = Santri::with('kelas')->findOrFail($santriId);
        $this->authorize('view', $santri);

        $records = $this->hafalanService->getHafalanQuranProgress($santriId);

        return view('dashboard.hafalan-quran.show', compact('santri', 'records'));
    }

    /**
     * Delete a record.
     */
    public function destroy(int $id)
    {
        $record = HafalanQuran::findOrFail($id);
        $this->authorize('delete', $record);
        $record->delete();

        return redirect(tenant_route('dashboard.kepesantrenan.hafalan-quran.index'))
            ->with('success', 'Record dihapus.');
    }

    /**
     * Input hafalan directly from a jadwal (schedule) row.
     * Pre-fills ustadz_kelas, kelas, subject from schedule context.
     */
    public function fromJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $this->authorize('viewAny', HafalanQuran::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $jadwal = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject', 'ustadzKelas.ustadz.user'])->findOrFail($jadwalId);

        $this->authorize('view', $jadwal);

        $santriList = Santri::where('kelas_id', $jadwal->kelas_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $tanggal = $request->get('tanggal', today()->toDateString());

        return view('dashboard.hafalan-quran.from-jadwal', compact(
            'jadwal', 'programSlug', 'program', 'santriList', 'tanggal'
        ));
    }

    /**
     * Store hafalan from jadwal context.
     */
    public function storeFromJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();
        $jadwal = Schedule::with('ustadzKelas')->findOrFail($jadwalId);

        $this->authorize('view', $jadwal);

        $data = $request->validate([
            'santri_id'      => ['required', 'integer', 'exists:santri,id'],
            'tanggal'        => ['required', 'date'],
            'surah'          => ['required', 'string', 'max:100'],
            'ayat_dari'      => ['nullable', 'string', 'max:10'],
            'ayat_sampai'    => ['nullable', 'string', 'max:10'],
            'juz'            => ['nullable', 'integer', 'min:1', 'max:30'],
            'nilai'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status'         => ['required', Rule::in(HafalanQuran::STATUS)],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ]);

        $data['ustadz_kelas_id'] = $jadwal->ustadz_kelas_id;
        $data['jadwal_id'] = $jadwalId;

        $this->hafalanService->storeHafalanQuran($data);

        return redirect(tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]) . '?hari=' . $jadwal->hari)
            ->with('success', 'Hafalan Quran berhasil disimpan.');
    }
}
