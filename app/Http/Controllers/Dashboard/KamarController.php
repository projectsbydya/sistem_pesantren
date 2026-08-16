<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreKamarRequest;
use App\Http\Requests\Dashboard\UpdateKamarRequest;
use App\Models\Kamar;
use App\Services\KamarService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class KamarController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private KamarService $kamarService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Kamar::class);

        $query = Kamar::withCount([
            'santri as terisi' => fn ($q) => $q->whereNotNull('kamar_id')
        ]);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $kamar = $query->orderBy('name')->paginate(20)->withQueryString();
        $statistics = $this->kamarService->getStatistics();

        return view('dashboard.kepesantrenan.kamar.index', compact('kamar', 'statistics'));
    }

    public function create(): View
    {
        $this->authorize('create', Kamar::class);

        return view('dashboard.kepesantrenan.kamar.create');
    }

    public function store(StoreKamarRequest $request): RedirectResponse
    {
        $this->authorize('create', Kamar::class);

        $this->kamarService->create($request->validated());

        return redirect()->route('dashboard.kepesantrenan.kamar.index')
            ->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function show(Kamar $kamar): View
    {
        $this->authorize('view', $kamar);

        $kamar = $this->kamarService->getWithOccupants($kamar->id);

        return view('dashboard.kepesantrenan.kamar.show', compact('kamar'));
    }

    public function edit(Kamar $kamar): View
    {
        $this->authorize('update', $kamar);

        return view('dashboard.kepesantrenan.kamar.edit', compact('kamar'));
    }

    public function update(UpdateKamarRequest $request, Kamar $kamar): RedirectResponse
    {
        $this->authorize('update', $kamar);

        $this->kamarService->update($kamar, $request->validated());

        return redirect()->route('dashboard.kepesantrenan.kamar.index')
            ->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Kamar $kamar): RedirectResponse
    {
        $this->authorize('delete', $kamar);

        try {
            $this->kamarService->delete($kamar);
            return redirect()->route('dashboard.kepesantrenan.kamar.index')
                ->with('success', 'Kamar berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function available(): JsonResponse
    {
        $this->authorize('viewAny', Kamar::class);

        $kamar = $this->kamarService->getAvailable();

        return response()->json($kamar);
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', Kamar::class);

        $statistics = $this->kamarService->getStatistics();

        return response()->json($statistics);
    }
}
