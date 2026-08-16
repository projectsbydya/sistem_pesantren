<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StorePelanggaranRequest;
use App\Http\Requests\Dashboard\UpdatePelanggaranRequest;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Services\PelanggaranService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PelanggaranController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PelanggaranService $pelanggaranService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pelanggaran::class);

        $user = auth()->user();
        $query = Pelanggaran::with(['santri', 'pelapor']);

        // Scope by user relation
        if ($user->santri) {
            $query->where('santri_id', $user->santri->id);
        } elseif ($user->parent) {
            $childIds = $user->parent->santri()->pluck('santri.id');
            $query->whereIn('santri_id', $childIds);
        }

        // Search by santri name or deskripsi
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('santri', function ($sq) use ($search) {
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
            $query->whereDate('tanggal', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('tanggal', '<=', $to);
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        $statistics = $this->pelanggaranService->getStatistics($request->get('from'), $request->get('to'));

        return view('dashboard.kepesantrenan.pelanggaran.index', compact('pelanggaran', 'statistics'));
    }

    public function create(): View
    {
        $this->authorize('create', Pelanggaran::class);

        return view('dashboard.kepesantrenan.pelanggaran.create');
    }

    public function store(StorePelanggaranRequest $request): RedirectResponse
    {
        $this->authorize('create', Pelanggaran::class);

        $this->pelanggaranService->create($request->validated());

        return redirect()->route('dashboard.kepesantrenan.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil ditambahkan.');
    }

    public function show(Pelanggaran $pelanggaran): View
    {
        $this->authorize('view', $pelanggaran);

        return view('dashboard.kepesantrenan.pelanggaran.show', compact('pelanggaran'));
    }

    public function edit(Pelanggaran $pelanggaran): View
    {
        $this->authorize('update', $pelanggaran);

        return view('dashboard.kepesantrenan.pelanggaran.edit', compact('pelanggaran'));
    }

    public function update(UpdatePelanggaranRequest $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $this->authorize('update', $pelanggaran);

        $this->pelanggaranService->update($pelanggaran, $request->validated());

        return redirect()->route('dashboard.kepesantrenan.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran): RedirectResponse
    {
        $this->authorize('delete', $pelanggaran);

        $this->pelanggaranService->delete($pelanggaran);

        return redirect()->route('dashboard.kepesantrenan.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil dihapus.');
    }

    public function process(Pelanggaran $pelanggaran, Request $request): RedirectResponse
    {
        $this->authorize('process', $pelanggaran);

        $this->pelanggaranService->process($pelanggaran, $request->input('tindak_lanjut'));

        return redirect()->route('dashboard.kepesantrenan.pelanggaran.show', $pelanggaran)
            ->with('success', 'Pelanggaran berhasil diproses.');
    }

    public function complete(Pelanggaran $pelanggaran): RedirectResponse
    {
        $this->authorize('complete', $pelanggaran);

        $this->pelanggaranService->complete($pelanggaran);

        return redirect()->route('dashboard.kepesantrenan.pelanggaran.show', $pelanggaran)
            ->with('success', 'Pelanggaran berhasil diselesaikan.');
    }

    public function bySantri(int $santriId): View
    {
        $this->authorize('viewAny', Pelanggaran::class);

        $santri = Santri::findOrFail($santriId);
        $pelanggaran = $this->pelanggaranService->getBySantri($santriId);

        return view('dashboard.kepesantrenan.pelanggaran.by-santri', compact('pelanggaran', 'santriId', 'santri'));
    }

    public function statistics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pelanggaran::class);

        $from = $request->input('from');
        $to = $request->input('to');

        $statistics = $this->pelanggaranService->getStatistics($from, $to);

        return response()->json($statistics);
    }
}
