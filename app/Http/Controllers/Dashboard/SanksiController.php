<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreSanksiRequest;
use App\Http\Requests\Dashboard\UpdateSanksiRequest;
use App\Models\Pelanggaran;
use App\Models\Sanksi;
use App\Models\Santri;
use App\Services\SanksiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SanksiController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private SanksiService $sanksiService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sanksi::class);

        $user = auth()->user();
        $query = Sanksi::with(['pelanggaran.santri', 'diberikanOleh']);

        // Scope by user relation (via pelanggaran.santri_id)
        if ($user->santri) {
            $query->whereHas('pelanggaran', fn ($q) => $q->where('santri_id', $user->santri->id));
        } elseif ($user->parent) {
            $childIds = $user->parent->santri()->pluck('santri.id');
            $query->whereHas('pelanggaran', fn ($q) => $q->whereIn('santri_id', $childIds));
        }

        // Search by santri name or pelanggaran deskripsi
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pelanggaran.santri', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('nis', 'like', "%{$search}%");
                })
                ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter by jenis
        if ($jenis = $request->get('jenis')) {
            $query->where('jenis', $jenis);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('tanggal_mulai', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal_mulai', '<=', $to);
        }

        $sanksi = $query->orderBy('tanggal_mulai', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statistics = $this->sanksiService->getStatistics($request->get('from'), $request->get('to'));

        return view('dashboard.kepesantrenan.sanksi.index', compact('sanksi', 'statistics'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Sanksi::class);

        $pelanggaranId = $request->input('pelanggaran_id');
        $pelanggaran = $pelanggaranId ? Pelanggaran::find($pelanggaranId) : null;

        return view('dashboard.kepesantrenan.sanksi.create', compact('pelanggaran'));
    }

    public function store(StoreSanksiRequest $request): RedirectResponse
    {
        $this->authorize('create', Sanksi::class);

        $sanksi = $this->sanksiService->create($request->validated());

        return redirect()->route('dashboard.kepesantrenan.sanksi.show', $sanksi)
            ->with('success', 'Sanksi berhasil ditambahkan.');
    }

    public function show(Sanksi $sanksi): View
    {
        $this->authorize('view', $sanksi);

        return view('dashboard.kepesantrenan.sanksi.show', compact('sanksi'));
    }

    public function edit(Sanksi $sanksi): View
    {
        $this->authorize('update', $sanksi);

        return view('dashboard.kepesantrenan.sanksi.edit', compact('sanksi'));
    }

    public function update(UpdateSanksiRequest $request, Sanksi $sanksi): RedirectResponse
    {
        $this->authorize('update', $sanksi);

        $this->sanksiService->update($sanksi, $request->validated());

        return redirect()->route('dashboard.kepesantrenan.sanksi.show', $sanksi)
            ->with('success', 'Sanksi berhasil diperbarui.');
    }

    public function destroy(Sanksi $sanksi): RedirectResponse
    {
        $this->authorize('delete', $sanksi);

        $this->sanksiService->delete($sanksi);

        return redirect()->route('dashboard.kepesantrenan.sanksi.index')
            ->with('success', 'Sanksi berhasil dihapus.');
    }

    public function complete(Sanksi $sanksi, Request $request): RedirectResponse
    {
        $this->authorize('complete', $sanksi);

        $this->sanksiService->complete($sanksi, $request->input('hasil_evaluasi'));

        return redirect()->route('dashboard.kepesantrenan.sanksi.show', $sanksi)
            ->with('success', 'Sanksi berhasil diselesaikan.');
    }

    public function cancel(Sanksi $sanksi, Request $request): RedirectResponse
    {
        $this->authorize('cancel', $sanksi);

        $this->sanksiService->cancel($sanksi, $request->input('alasan'));

        return redirect()->route('dashboard.kepesantrenan.sanksi.show', $sanksi)
            ->with('success', 'Sanksi berhasil dibatalkan.');
    }

    public function bySantri(int $santriId): View
    {
        $this->authorize('viewAny', Sanksi::class);

        $santri = Santri::findOrFail($santriId);
        $sanksi = $this->sanksiService->getBySantri($santriId);

        return view('dashboard.kepesantrenan.sanksi.by-santri', compact('sanksi', 'santriId', 'santri'));
    }

    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Sanksi::class);

        $from = $request->input('from');
        $to = $request->input('to');

        $statistics = $this->sanksiService->getStatistics($from, $to);

        return response()->json($statistics);
    }
}
