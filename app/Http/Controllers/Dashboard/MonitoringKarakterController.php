<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreMonitoringKarakterRequest;
use App\Models\MonitoringKarakter;
use App\Models\Santri;
use App\Services\MonitoringKarakterService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MonitoringKarakterController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private MonitoringKarakterService $monitoringService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MonitoringKarakter::class);

        $user = auth()->user();
        $query = MonitoringKarakter::with(['santri', 'dinilaiOleh']);

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

        // Filter by aspek
        if ($aspek = $request->get('aspek')) {
            $query->where('aspek', $aspek);
        }

        // Filter by periode
        if ($periode = $request->get('periode')) {
            $query->where('periode', $periode);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal', '<=', $to);
        }

        $monitoring = $query->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statsQuery = MonitoringKarakter::query();
        if ($user->santri) {
            $statsQuery->where('santri_id', $user->santri->id);
        } elseif ($user->parent) {
            $statsQuery->whereIn('santri_id', $user->parent->santri()->pluck('santri.id'));
        }
        $statistics = [
            'total'    => (clone $statsQuery)->count(),
            'by_aspek' => (clone $statsQuery)->selectRaw('aspek, count(*) as total')
                ->groupBy('aspek')
                ->pluck('total', 'aspek'),
        ];

        return view('dashboard.kepesantrenan.monitoring-karakter.index', compact('monitoring', 'statistics'));
    }

    public function create(): View
    {
        $this->authorize('create', MonitoringKarakter::class);

        $santri = Santri::where('status', 'active')->orderBy('name')->get();

        return view('dashboard.kepesantrenan.monitoring-karakter.create', compact('santri'));
    }

    public function store(StoreMonitoringKarakterRequest $request): RedirectResponse
    {
        $this->authorize('create', MonitoringKarakter::class);

        $this->monitoringService->create($request->validated());

        return redirect()->route('dashboard.kepesantrenan.monitoring-karakter.index')
            ->with('success', 'Monitoring karakter berhasil ditambahkan.');
    }

    public function show(MonitoringKarakter $monitoring): View
    {
        $this->authorize('view', $monitoring);

        return view('dashboard.kepesantrenan.monitoring-karakter.show', compact('monitoring'));
    }

    public function edit(MonitoringKarakter $monitoring): View
    {
        $this->authorize('update', $monitoring);

        return view('dashboard.kepesantrenan.monitoring-karakter.edit', compact('monitoring'));
    }

    public function update(StoreMonitoringKarakterRequest $request, MonitoringKarakter $monitoring): RedirectResponse
    {
        $this->authorize('update', $monitoring);

        $this->monitoringService->update($monitoring, $request->validated());

        return redirect()->route('dashboard.kepesantrenan.monitoring-karakter.index')
            ->with('success', 'Monitoring karakter berhasil diperbarui.');
    }

    public function destroy(MonitoringKarakter $monitoring): RedirectResponse
    {
        $this->authorize('delete', $monitoring);

        $this->monitoringService->delete($monitoring);

        return redirect()->route('dashboard.kepesantrenan.monitoring-karakter.index')
            ->with('success', 'Monitoring karakter berhasil dihapus.');
    }

    public function bySantri(int $santriId): View
    {
        $this->authorize('viewAny', MonitoringKarakter::class);

        $santri = Santri::findOrFail($santriId);
        $monitoring = $this->monitoringService->getBySantri($santriId);
        $rekap = $this->monitoringService->getRekapBySantri($santriId);

        return view('dashboard.kepesantrenan.monitoring-karakter.by-santri', compact('monitoring', 'rekap', 'santriId', 'santri'));
    }
}
