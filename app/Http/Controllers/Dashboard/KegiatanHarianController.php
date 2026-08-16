<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreKegiatanHarianRequest;
use App\Http\Requests\Dashboard\UpdateKegiatanHarianRequest;
use App\Models\KegiatanHarian;
use App\Models\Santri;
use App\Services\KegiatanHarianService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class KegiatanHarianController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private KegiatanHarianService $kegiatanService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', KegiatanHarian::class);

        $user = auth()->user();
        $query = KegiatanHarian::with(['santri', 'dicatatOleh']);

        // Scope by user relation
        if ($user->santri) {
            $query->where('santri_id', $user->santri->id);
        } elseif ($user->parent) {
            $childIds = $user->parent->santri()->pluck('santri.id');
            $query->whereIn('santri_id', $childIds);
        }

        // Search by santri name
        if ($search = $request->get('search')) {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter by jenis kegiatan
        if ($jenis = $request->get('jenis')) {
            $query->where('jenis_kegiatan', $jenis);
        }

        // Filter by kategori
        if ($kategori = $request->get('kategori')) {
            $query->where('kategori', $kategori);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal', '<=', $to);
        }

        $kegiatan = $query->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai', 'asc')
            ->paginate(20)
            ->withQueryString();

        $statsQuery = KegiatanHarian::query();
        if ($user->santri) {
            $statsQuery->where('santri_id', $user->santri->id);
        } elseif ($user->parent) {
            $statsQuery->whereIn('santri_id', $user->parent->santri()->pluck('santri.id'));
        }
        $statistics = [
            'total'     => (clone $statsQuery)->count(),
            'by_status' => [
                'terjadwal'          => (clone $statsQuery)->where('status', 'terjadwal')->count(),
                'dilaksanakan'       => (clone $statsQuery)->where('status', 'dilaksanakan')->count(),
                'tidak_dilaksanakan' => (clone $statsQuery)->where('status', 'tidak_dilaksanakan')->count(),
            ],
        ];

        return view('dashboard.kepesantrenan.kegiatan-harian.index', compact('kegiatan', 'statistics'));
    }

    public function create(): View
    {
        $this->authorize('create', KegiatanHarian::class);

        $santri = Santri::where('status', 'active')->orderBy('name')->get();

        return view('dashboard.kepesantrenan.kegiatan-harian.create', compact('santri'));
    }

    public function store(StoreKegiatanHarianRequest $request): RedirectResponse
    {
        $this->authorize('create', KegiatanHarian::class);

        $this->kegiatanService->create($request->validated());

        return redirect()->route('dashboard.kepesantrenan.kegiatan-harian.index')
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(KegiatanHarian $kegiatan): View
    {
        $this->authorize('view', $kegiatan);

        return view('dashboard.kepesantrenan.kegiatan-harian.show', compact('kegiatan'));
    }

    public function edit(KegiatanHarian $kegiatan): View
    {
        $this->authorize('update', $kegiatan);

        return view('dashboard.kepesantrenan.kegiatan-harian.edit', compact('kegiatan'));
    }

    public function update(UpdateKegiatanHarianRequest $request, KegiatanHarian $kegiatan): RedirectResponse
    {
        $this->authorize('update', $kegiatan);

        $this->kegiatanService->update($kegiatan, $request->validated());

        return redirect()->route('dashboard.kepesantrenan.kegiatan-harian.index')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(KegiatanHarian $kegiatan): RedirectResponse
    {
        $this->authorize('delete', $kegiatan);

        $this->kegiatanService->delete($kegiatan);

        return redirect()->route('dashboard.kepesantrenan.kegiatan-harian.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function markAsDone(KegiatanHarian $kegiatan, Request $request): RedirectResponse
    {
        $this->authorize('markStatus', $kegiatan);

        $this->kegiatanService->markAsDone($kegiatan, $request->input('catatan'));

        return redirect()->route('dashboard.kepesantrenan.kegiatan-harian.index')
            ->with('success', 'Kegiatan berhasil ditandai sebagai selesai.');
    }

    public function markAsMissed(KegiatanHarian $kegiatan, Request $request): RedirectResponse
    {
        $this->authorize('markStatus', $kegiatan);

        $this->kegiatanService->markAsMissed($kegiatan, $request->input('catatan'));

        return redirect()->route('dashboard.kepesantrenan.kegiatan-harian.index')
            ->with('success', 'Kegiatan berhasil ditandai sebagai tidak dilaksanakan.');
    }

    public function bySantri(int $santriId): View
    {
        $this->authorize('viewAny', KegiatanHarian::class);

        $santri = Santri::findOrFail($santriId);
        $kegiatan = $this->kegiatanService->getBySantri($santriId);

        return view('dashboard.kepesantrenan.kegiatan-harian.by-santri', compact('kegiatan', 'santriId', 'santri'));
    }

    public function byDate(string $date): JsonResponse
    {
        $this->authorize('viewAny', KegiatanHarian::class);

        $kegiatan = $this->kegiatanService->getByDate($date);

        return response()->json($kegiatan);
    }
}
