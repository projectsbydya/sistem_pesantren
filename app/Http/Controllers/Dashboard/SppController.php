<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Services\BillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SppController extends Controller
{
    public function __construct(
        private BillService $billService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Bill::class);

        return view('dashboard.spp.index', $this->billService->indexData(
            Auth::user(),
            $request->only([
                'santri_id',
                'kelas_id',
                'status',
                'type',
            ])
        ));
    }

    public function create()
    {
        $this->authorize('create', Bill::class);

        return view('dashboard.spp.create', [
            'santriList' => $this->billService->getSantriList(),
            'kelasList'  => $this->billService->getKelasList(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Bill::class);

        $validated = $request->validate($this->billService->createRules());

        $this->billService->create($validated);

        return redirect()->route('dashboard.spp.index')
            ->with('success', 'Tagihan berhasil dibuat.');
    }

    public function show(Bill $bill)
    {
        $this->authorize('view', $bill);

        $bill->load([
            'santri',
            'billPayments' => fn ($query) => $query->orderByDesc('submitted_at'),
            'billPayments.verifiedBy',
        ]);

        return view('dashboard.spp.show', compact('bill'));
    }

    public function edit(Bill $bill)
    {
        $this->authorize('update', $bill);
        return view('dashboard.spp.edit', [
            'bill' => $bill,
            'santriList' => $this->billService->getSantriList(),
            'kelasList'  => $this->billService->getKelasList(),
        ]);
    }

    public function update(Request $request, Bill $bill)
    {
        $this->authorize('update', $bill);

        $validated = $request->validate($this->billService->updateRules());

        $this->billService->update($bill, $validated);

        return redirect()->route('dashboard.spp.index')
            ->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function destroy(Bill $bill)
    {
        $this->authorize('delete', $bill);
        $this->billService->delete($bill);

        return redirect()->route('dashboard.spp.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', Bill::class);

        $filename = 'rekap-spp-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(
            $this->billService->export($request->only(['status', 'type', 'santri_id'])),
            $filename
        );
    }
}
