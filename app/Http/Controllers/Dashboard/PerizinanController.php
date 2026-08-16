<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StorePerizinanRequest;
use App\Models\Perizinan;
use App\Services\PerizinanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PerizinanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PerizinanService $perizinanService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Perizinan::class);

        $user = auth()->user();
        $query = Perizinan::with(['santri', 'diajukanOleh', 'disetujuiOleh']);

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

        $perizinan = $query->orderBy('tanggal_mulai', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statsQuery = Perizinan::query();
        if ($user->santri) {
            $statsQuery->where('santri_id', $user->santri->id);
        } elseif ($user->parent) {
            $statsQuery->whereIn('santri_id', $user->parent->santri()->pluck('santri.id'));
        }
        $statistics = [
            'total'     => (clone $statsQuery)->count(),
            'pending'   => (clone $statsQuery)->where('status', Perizinan::STATUS_PENDING)->count(),
            'disetujui' => (clone $statsQuery)->where('status', Perizinan::STATUS_DISETUJUI)->count(),
            'ditolak'   => (clone $statsQuery)->where('status', Perizinan::STATUS_DITOLAK)->count(),
            'kembali'   => (clone $statsQuery)->where('status', Perizinan::STATUS_KEMBALI)->count(),
        ];

        return view('dashboard.kepesantrenan.perizinan.index', compact('perizinan', 'statistics'));
    }

    public function create(): View
    {
        $this->authorize('create', Perizinan::class);

        return view('dashboard.kepesantrenan.perizinan.create');
    }

    public function store(StorePerizinanRequest $request): RedirectResponse
    {
        $this->authorize('create', Perizinan::class);

        $perizinan = $this->perizinanService->create($request->validated());

        return redirect()->route('dashboard.kepesantrenan.perizinan.show', $perizinan)
            ->with('success', 'Pengajuan izin berhasil dibuat.');
    }

    public function show(Perizinan $perizinan): View
    {
        $this->authorize('view', $perizinan);

        return view('dashboard.kepesantrenan.perizinan.show', compact('perizinan'));
    }

    public function edit(Perizinan $perizinan): View
    {
        $this->authorize('update', $perizinan);

        return view('dashboard.kepesantrenan.perizinan.edit', compact('perizinan'));
    }

    public function update(StorePerizinanRequest $request, Perizinan $perizinan): RedirectResponse
    {
        $this->authorize('update', $perizinan);

        $this->perizinanService->update($perizinan, $request->validated());

        return redirect()->route('dashboard.kepesantrenan.perizinan.show', $perizinan)
            ->with('success', 'Pengajuan izin berhasil diperbarui.');
    }

    public function destroy(Perizinan $perizinan): RedirectResponse
    {
        $this->authorize('delete', $perizinan);

        try {
            $this->perizinanService->delete($perizinan);
            return redirect()->route('dashboard.kepesantrenan.perizinan.index')
                ->with('success', 'Pengajuan izin berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Aksi tidak dapat dilakukan pada status izin saat ini.');
        }
    }

    public function approve(Perizinan $perizinan, Request $request): RedirectResponse
    {
        $this->authorize('approve', $perizinan);

        try {
            $this->perizinanService->approve($perizinan, $request->input('catatan'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Aksi tidak dapat dilakukan pada status izin saat ini.');
        }

        return redirect()->route('dashboard.kepesantrenan.perizinan.show', $perizinan)
            ->with('success', 'Izin berhasil disetujui.');
    }

    public function reject(Perizinan $perizinan, Request $request): RedirectResponse
    {
        $this->authorize('reject', $perizinan);

        try {
            $this->perizinanService->reject($perizinan, $request->input('alasan'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Aksi tidak dapat dilakukan pada status izin saat ini.');
        }

        return redirect()->route('dashboard.kepesantrenan.perizinan.show', $perizinan)
            ->with('success', 'Izin berhasil ditolak.');
    }

    public function recordReturn(Perizinan $perizinan): RedirectResponse
    {
        $this->authorize('recordReturn', $perizinan);

        try {
            $this->perizinanService->recordReturn($perizinan);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Aksi tidak dapat dilakukan pada status izin saat ini.');
        }

        return redirect()->route('dashboard.kepesantrenan.perizinan.show', $perizinan)
            ->with('success', 'Kepulangan santri berhasil dicatat.');
    }

    public function pending(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Perizinan::class);

        $user = auth()->user();

        if ($user->santri || $user->parent) {
            return redirect()->route('dashboard.kepesantrenan.perizinan.index');
        }
        $query = Perizinan::with(['santri', 'diajukanOleh'])
            ->where('status', Perizinan::STATUS_PENDING);

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

        $perizinan = $query->orderBy('tanggal_mulai', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.kepesantrenan.perizinan.pending', compact('perizinan'));
    }

    public function bySantri(int $santriId): View
    {
        $this->authorize('viewForSantri', [Perizinan::class, $santriId]);

        $perizinan = $this->perizinanService->getBySantri($santriId);
        $santri = \App\Models\Santri::find($santriId);

        return view('dashboard.kepesantrenan.perizinan.by-santri', compact('perizinan', 'santri'));
    }
}
