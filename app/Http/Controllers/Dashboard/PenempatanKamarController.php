<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\MovePenempatanKamarRequest;
use App\Http\Requests\Dashboard\StorePenempatanKamarRequest;
use App\Models\PenempatanKamar;
use App\Models\Santri;
use App\Services\KamarService;
use App\Services\PenempatanKamarService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class PenempatanKamarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private PenempatanKamarService $penempatanService,
        private KamarService $kamarService
    ) {}

    public function index(\Illuminate\Http\Request $request): View
    {
        $this->authorize('viewAny', PenempatanKamar::class);

        $query = PenempatanKamar::whereNull('tanggal_keluar')
            ->with(['santri', 'kamar']);

        // Search by santri name
        if ($search = $request->get('search')) {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter by kamar
        if ($kamarId = $request->get('kamar_id')) {
            $query->where('kamar_id', $kamarId);
        }

        $penempatan = $query->orderBy('tanggal_masuk', 'desc')
            ->paginate(20)
            ->withQueryString();

        $kamarList = $this->kamarService->getActive();

        return view('dashboard.kepesantrenan.penempatan.index', compact('penempatan', 'kamarList'));
    }

    public function create(): View
    {
        $this->authorize('create', PenempatanKamar::class);

        $kamar = $this->kamarService->getAvailable();
        $santri = $this->kamarService->getSantriWithoutRoom();

        return view('dashboard.kepesantrenan.penempatan.create', compact('kamar', 'santri'));
    }

    public function store(StorePenempatanKamarRequest $request): RedirectResponse
    {
        $this->authorize('create', PenempatanKamar::class);

        try {
            $this->penempatanService->assign($request->validated());
            return redirect()->route('dashboard.kepesantrenan.penempatan.index')
                ->with('success', 'Penempatan kamar berhasil ditambahkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(PenempatanKamar $penempatan): View
    {
        $this->authorize('view', $penempatan);

        $history = $this->penempatanService->getHistory($penempatan->santri_id);

        return view('dashboard.kepesantrenan.penempatan.show', compact('penempatan', 'history'));
    }

    public function destroy(PenempatanKamar $penempatan): RedirectResponse
    {
        $this->authorize('delete', $penempatan);

        $this->penempatanService->checkout($penempatan->santri_id);

        return redirect()->route('dashboard.kepesantrenan.penempatan.index')
            ->with('success', 'Penempatan kamar berhasil dihapus.');
    }

    public function moveForm(): View
    {
        $this->authorize('create', PenempatanKamar::class);

        // Only santri who currently have a room can be moved to another one.
        $santri = Santri::where('status', 'active')
            ->whereNotNull('kamar_id')
            ->with('kamar')
            ->orderBy('name')
            ->get();
        $kamar = $this->kamarService->getAvailable();

        return view('dashboard.kepesantrenan.penempatan.move', compact('kamar', 'santri'));
    }

    public function move(MovePenempatanKamarRequest $request): RedirectResponse
    {
        $this->authorize('create', PenempatanKamar::class);

        try {
            $result = $this->penempatanService->move(
                $request->integer('santri_id'),
                $request->integer('kamar_tujuan_id'),
                $request->alasan,
                $request->keterangan
            );

            return redirect()->route('dashboard.kepesantrenan.penempatan.index')
                ->with('success', 'Mutasi kamar berhasil dilakukan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function byKamar(int $kamarId): JsonResponse
    {
        $this->authorize('viewAny', PenempatanKamar::class);

        $penempatan = $this->penempatanService->getByKamar($kamarId);

        return response()->json($penempatan);
    }
}
