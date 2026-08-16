<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MutasiKamar;
use App\Models\Santri;
use App\Services\KamarService;
use App\Services\MutasiKamarService;
use App\Services\PenempatanKamarService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MutasiKamarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private MutasiKamarService $mutasiService,
        private KamarService $kamarService,
        private PenempatanKamarService $penempatanService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MutasiKamar::class);

        $query = MutasiKamar::with([
            'santri',
            'kamarAsal',
            'kamarTujuan',
            'processedBy',
        ]);

        // Search by santri name
        if ($search = $request->get('search')) {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter by kamar (asal or tujuan)
        if ($kamarId = $request->get('kamar_id')) {
            $query->where(function ($q) use ($kamarId) {
                $q->where('kamar_asal_id', $kamarId)
                  ->orWhere('kamar_tujuan_id', $kamarId);
            });
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('tanggal_mutasi', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal_mutasi', '<=', $to);
        }

        $mutasi = $query->orderBy('tanggal_mutasi', 'desc')
            ->paginate(20)
            ->withQueryString();

        $kamarList = $this->kamarService->getActive();
        $statistics = $this->mutasiService->getStatistics($request->get('from'), $request->get('to'));

        return view('dashboard.kepesantrenan.mutasi.index', compact('mutasi', 'kamarList', 'statistics'));
    }

    public function show(MutasiKamar $mutasi): View
    {
        $this->authorize('view', $mutasi);

        // Prefer the santri's current (active) placement, but fall back to the
        // most recent placement record so the history link still works even
        // if the santri has since been checked out.
        $currentPlacement = $this->penempatanService->getCurrentBySantri($mutasi->santri_id)
            ?? \App\Models\PenempatanKamar::where('santri_id', $mutasi->santri_id)
                ->latest('tanggal_masuk')
                ->first();

        return view('dashboard.kepesantrenan.mutasi.show', compact('mutasi', 'currentPlacement'));
    }

    public function create(): View
    {
        $this->authorize('create', MutasiKamar::class);

        // Get santri who currently have a room (can be moved)
        $santri = Santri::where('status', 'active')
            ->whereNotNull('kamar_id')
            ->with('kamar')
            ->orderBy('name')
            ->get();

        $kamar = $this->kamarService->getAvailable();

        return view('dashboard.kepesantrenan.mutasi.create', compact('santri', 'kamar'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MutasiKamar::class);

        $validated = $request->validate([
            'santri_id' => 'required|exists:santri,id',
            'kamar_tujuan_id' => 'required|exists:kamar,id',
            'tanggal_mutasi' => 'required|date',
            'alasan' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $this->penempatanService->move(
                (int) $validated['santri_id'],
                (int) $validated['kamar_tujuan_id'],
                $validated['alasan'],
                $validated['keterangan'] ?? null
            );

            return redirect()->route('dashboard.kepesantrenan.mutasi.index')
                ->with('success', 'Mutasi kamar berhasil ditambahkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function bySantri(int $santriId): View
    {
        $this->authorize('viewAny', MutasiKamar::class);

        $santri = Santri::findOrFail($santriId);
        $mutasi = $this->mutasiService->getBySantri($santriId);

        return view('dashboard.kepesantrenan.mutasi.by-santri', compact('mutasi', 'santriId', 'santri'));
    }

    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MutasiKamar::class);

        $from = $request->input('from');
        $to = $request->input('to');

        $statistics = $this->mutasiService->getStatistics($from, $to);

        return response()->json($statistics);
    }
}
