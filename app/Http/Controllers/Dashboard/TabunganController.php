<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\TabunganExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriProgram;
use App\Models\Tabungan;
use App\Services\TabunganService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class TabunganController extends Controller
{
    public function __construct(private TabunganService $tabunganService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Tabungan::class);

        $accessibleSantriIds = $this->accessibleSantriIds();
        $isPersonalView = ! auth()->user()?->can('create', Tabungan::class);
        $tenant = TenantService::getTenant();

        $santriList = Santri::when($accessibleSantriIds !== null, fn ($q) => $q->whereIn('id', $accessibleSantriIds))
            ->orderBy('name')
            ->get();
        $kelasList  = $this->kelasListWithSantri($accessibleSantriIds);

        $query = Tabungan::with('santri')->orderByDesc('tanggal');

        if ($accessibleSantriIds !== null) {
            $query->whereIn('santri_id', $accessibleSantriIds);
        }

        $selectedSantriId = $request->filled('santri_id') ? (int) $request->input('santri_id') : null;

        if ($isPersonalView && $selectedSantriId === null && $santriList->isNotEmpty()) {
            $selectedSantriId = $santriList->first()->id;
        }

        if ($selectedSantriId !== null) {
            $query->where('santri_id', $selectedSantriId);
        }

        if ($request->filled('kelas_id')) {
            $kelasId = (int) $request->input('kelas_id');
            $query->whereHas('santri.programs', fn ($q) => $q->where('kelas_id', $kelasId));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        $tabungans = $query->paginate(20)->withQueryString();

        $baseQuery = Tabungan::query();
        if ($accessibleSantriIds !== null) {
            $baseQuery->whereIn('santri_id', $accessibleSantriIds);
        }
        if ($selectedSantriId !== null) {
            $baseQuery->where('santri_id', $selectedSantriId);
        }

        $totalSetor   = (clone $baseQuery)->setor()->sum('jumlah');
        $totalTarik   = (clone $baseQuery)->tarik()->sum('jumlah');
        $totalSaldo   = $totalSetor - $totalTarik;
        $jumlahSantri = (clone $baseQuery)->distinct('santri_id')->count('santri_id');

        return view('dashboard.tabungan.index', compact(
            'tabungans', 'santriList', 'kelasList', 'tenant',
            'totalSetor', 'totalTarik', 'totalSaldo', 'jumlahSantri',
            'isPersonalView', 'selectedSantriId'
        ));
    }

    public function create()
    {
        $this->authorize('create', Tabungan::class);

        $santriList = Santri::orderBy('name')->get();
        $kelasList  = $this->kelasListWithSantri();
        return view('dashboard.tabungan.create', compact('santriList', 'kelasList'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tabungan::class);

        $validated = $request->validate([
            'santri_id'  => ['required', Rule::exists('santri', 'id')->where('tenant_id', tenant_id())],
            'jenis'      => 'required|in:setor,tarik',
            'jumlah'     => 'required|numeric|min:1',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($validated['jenis'] === 'tarik') {
            $saldo = Tabungan::where('santri_id', $validated['santri_id'])->setor()->sum('jumlah')
                   - Tabungan::where('santri_id', $validated['santri_id'])->tarik()->sum('jumlah');
            if ($validated['jumlah'] > $saldo) {
                return back()->withInput()->withErrors(['jumlah' => 'Jumlah penarikan melebihi saldo tabungan (Rp ' . number_format($saldo, 0, ',', '.') . ').']);
            }
        }

        $this->tabunganService->create($validated);

        return redirect()->route('dashboard.tabungan.index')
            ->with('success', 'Transaksi tabungan berhasil dicatat.');
    }

    public function show(Tabungan $tabungan)
    {
        $this->authorize('view', $tabungan);
        return view('dashboard.tabungan.show', compact('tabungan'));
    }

    public function edit(Tabungan $tabungan)
    {
        $this->authorize('update', $tabungan);

        $santriList = Santri::orderBy('name')->get();
        $kelasList  = $this->kelasListWithSantri();
        return view('dashboard.tabungan.edit', compact('tabungan', 'santriList', 'kelasList'));
    }

    public function update(Request $request, Tabungan $tabungan)
    {
        $this->authorize('update', $tabungan);

        $validated = $request->validate([
            'santri_id'  => ['required', Rule::exists('santri', 'id')->where('tenant_id', tenant_id())],
            'jenis'      => 'required|in:setor,tarik',
            'jumlah'     => 'required|numeric|min:1',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tabungan->update($validated);

        return redirect()->route('dashboard.tabungan.index')
            ->with('success', 'Transaksi tabungan berhasil diperbarui.');
    }

    public function destroy(Tabungan $tabungan)
    {
        $this->authorize('delete', $tabungan);
        $tabungan->delete();

        return redirect()->route('dashboard.tabungan.index')
            ->with('success', 'Transaksi tabungan berhasil dihapus.');
    }

    public function saldoSantri(Santri $santri)
    {
        $this->authorize('viewAny', Tabungan::class);

        $accessibleSantriIds = $this->accessibleSantriIds();
        if ($accessibleSantriIds !== null && ! in_array($santri->id, $accessibleSantriIds, true)) {
            abort(403, 'Anda tidak memiliki akses ke data tabungan santri ini.');
        }

        $riwayat = Tabungan::where('santri_id', $santri->id)
            ->orderByDesc('tanggal')->get();

        $totalSetor = $riwayat->where('jenis', 'setor')->sum('jumlah');
        $totalTarik = $riwayat->where('jenis', 'tarik')->sum('jumlah');
        $saldo      = $totalSetor - $totalTarik;

        return view('dashboard.tabungan.santri', compact(
            'santri', 'riwayat', 'totalSetor', 'totalTarik', 'saldo'
        ));
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', Tabungan::class);

        $filename = 'rekap-tabungan-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            new TabunganExport(
                jenis: $request->input('jenis'),
                santriId: $request->input('santri_id') ? (int) $request->input('santri_id') : null,
            ),
            $filename
        );
    }

    /**
     * Build the list of Kelas, each with its enrolled santri attached as a
     * virtual 'santri' relation.
     *
     * NOTE: Class placement is tracked via the santri_program pivot
     * (SantriProgram::kelas_id), NOT the legacy santri.kelas_id column
     * (which is unused/always null). This must read from that pivot.
     */
    private function kelasListWithSantri(?array $accessibleSantriIds = null)
    {
        $kelasList = Kelas::orderBy('name')->get();

        $programQuery = SantriProgram::whereNotNull('kelas_id')
            ->whereIn('kelas_id', $kelasList->pluck('id'))
            ->with('santri');

        if ($accessibleSantriIds !== null) {
            $programQuery->whereIn('santri_id', $accessibleSantriIds);
        }

        $santriByKelas = $programQuery->get()->groupBy('kelas_id');

        return $kelasList->map(function (Kelas $kelas) use ($santriByKelas) {
            $santri = ($santriByKelas[$kelas->id] ?? collect())
                ->pluck('santri')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();

            $kelas->setRelation('santri', $santri);

            return $kelas;
        });
    }

    /**
     * Returns null for users that may view all santri (admin/bendahara),
     * or an array of accessible santri IDs for scoped users (santri/parent).
     */
    private function accessibleSantriIds(): ?array
    {
        if (auth()->user()?->can('create', Tabungan::class)) {
            return null;
        }

        return auth()->user()?->getAccessibleSantriIds() ?? [];
    }
}
