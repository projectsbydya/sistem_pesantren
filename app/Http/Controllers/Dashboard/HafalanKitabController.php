<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HafalanKitab;
use App\Models\Program;
use App\Models\Santri;
use App\Models\Schedule;
use App\Services\HafalanService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HafalanKitabController extends Controller
{
    public function __construct(
        private HafalanService $hafalanService
    ) {}

    /**
     * List all hafalan kitab records.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', HafalanKitab::class);

        $records = $this->hafalanService->getHafalanKitabRecords();
        $santriList = $this->hafalanService->getSantriList();

        return view('dashboard.hafalan-kitab.index', compact('records', 'santriList'));
    }

    /**
     * Store a new hafalan kitab record.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'santri_id'      => ['required', 'integer', 'exists:santri,id'],
            'ustadz_kelas_id' => ['required', 'integer', 'exists:ustadz_kelas,id'],
            'tanggal'        => ['required', 'date'],
            'nama_kitab'     => ['required', 'string', 'max:150'],
            'bab'            => ['nullable', 'string', 'max:100'],
            'halaman'        => ['nullable', 'string', 'max:50'],
            'nilai'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status'         => ['required', Rule::in(HafalanKitab::STATUS)],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ]);

        $ustadzKelas = \App\Models\UstadzKelas::findOrFail($data['ustadz_kelas_id']);
        $this->authorize('recordFor', [HafalanKitab::class, $ustadzKelas]);

        $this->hafalanService->storeHafalanKitab($data);

        return redirect(tenant_route('dashboard.kepesantrenan.hafalan-kitab.index'))
            ->with('success', 'Hafalan Kitab berhasil disimpan.');
    }

    /**
     * Santri progress view.
     */
    public function show(int $santriId)
    {
        $santri = Santri::with('kelas')->findOrFail($santriId);
        $this->authorize('view', $santri);

        $records = $this->hafalanService->getHafalanKitabProgress($santriId);

        return view('dashboard.hafalan-kitab.show', compact('santri', 'records'));
    }

    /**
     * Delete a record.
     */
    public function destroy(int $id)
    {
        $record = HafalanKitab::findOrFail($id);
        $this->authorize('delete', $record);
        $record->delete();

        return redirect(tenant_route('dashboard.kepesantrenan.hafalan-kitab.index'))
            ->with('success', 'Record dihapus.');
    }

    /**
     * Input hafalan kitab directly from a jadwal (schedule) row.
     * Pre-fills ustadz_kelas, kelas, subject from schedule context.
     */
    public function fromJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $this->authorize('viewAny', HafalanKitab::class);

        $program = Program::where('slug', $programSlug)->firstOrFail();
        $jadwal = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject', 'ustadzKelas.ustadz.user'])->findOrFail($jadwalId);

        $this->authorize('view', $jadwal);

        $santriList = Santri::where('kelas_id', $jadwal->kelas_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $tanggal = $request->get('tanggal', today()->toDateString());

        return view('dashboard.hafalan-kitab.from-jadwal', compact(
            'jadwal', 'programSlug', 'program', 'santriList', 'tanggal'
        ));
    }

    /**
     * Store hafalan kitab from jadwal context.
     */
    public function storeFromJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $program = Program::where('slug', $programSlug)->firstOrFail();
        $jadwal = Schedule::with('ustadzKelas')->findOrFail($jadwalId);

        $this->authorize('view', $jadwal);

        $data = $request->validate([
            'santri_id'      => ['required', 'integer', 'exists:santri,id'],
            'tanggal'        => ['required', 'date'],
            'nama_kitab'     => ['required', 'string', 'max:150'],
            'bab'            => ['nullable', 'string', 'max:100'],
            'halaman'        => ['nullable', 'string', 'max:50'],
            'nilai'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status'         => ['required', Rule::in(HafalanKitab::STATUS)],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ]);

        $data['ustadz_kelas_id'] = $jadwal->ustadz_kelas_id;
        $data['jadwal_id'] = $jadwalId;

        $this->hafalanService->storeHafalanKitab($data);

        return redirect(tenant_route('dashboard.akademik.jadwal.index', ['programSlug' => $programSlug]) . '?hari=' . $jadwal->hari)
            ->with('success', 'Hafalan Kitab berhasil disimpan.');
    }
}
