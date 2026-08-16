<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Services\BillPaymentService;
use App\Services\BillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(
        private BillPaymentService $billPaymentService,
        private BillService $billService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', BillPayment::class);

        return view('dashboard.payments.index', [
            'payments' => $this->billPaymentService->getPaymentsFor(
                Auth::user(),
                $request->filled('status') ? $request->input('status') : null
            ),
        ]);
    }

    public function pending(Request $request)
    {
        $this->authorize('viewAny', BillPayment::class);

        return view('dashboard.payments.index', [
            'payments' => $this->billPaymentService->getPendingPayments(Auth::user()),
            'filterStatus' => 'pending',
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('submitPayment', BillPayment::class);

        $user = Auth::user();
        $selectedBill = null;
        if ($request->filled('bill_id')) {
            $selectedBill = $this->billPaymentService->findBill((int) $request->input('bill_id'));
        }

        return view('dashboard.payments.create', [
            'santriList' => $this->billService->getSantriListForUser($user),
            'billList' => $this->billService->getUnpaidBillsForUser($user),
            'selectedBill' => $selectedBill,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('submitPayment', BillPayment::class);

        $validated = $request->validate($this->billPaymentService->createRules());
        $bill = $this->billPaymentService->findBill((int) $validated['bill_id']);
        $this->authorize('create', [BillPayment::class, $bill]);

        if ($request->hasFile('transfer_proof')) {
            $validated['transfer_proof'] = $request->file('transfer_proof')->store('transfer-proofs', 'public');
        }

        try {
            $this->billPaymentService->create($bill, $validated);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('dashboard.payments.index')
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function show(BillPayment $payment)
    {
        $this->authorize('view', $payment);

        return view('dashboard.payments.show', compact('payment'));
    }

    public function edit(BillPayment $payment)
    {
        $this->authorize('update', $payment);

        $user = Auth::user();

        return view('dashboard.payments.edit', [
            'payment' => $payment,
            'santriList' => $this->billService->getSantriListForUser($user),
            'billList' => $this->billService->getUnpaidBillsForUser($user),
        ]);
    }

    public function update(Request $request, BillPayment $payment)
    {
        $this->authorize('update', $payment);

        $validated = $request->validate($this->billPaymentService->updateRules());

        if ($request->hasFile('transfer_proof')) {
            $validated['transfer_proof'] = $request->file('transfer_proof')->store('transfer-proofs', 'public');
        }

        try {
            $this->billPaymentService->update($payment, $validated);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('dashboard.payments.index')
            ->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(BillPayment $payment)
    {
        $this->authorize('delete', $payment);

        try {
            $this->billPaymentService->delete($payment);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('dashboard.payments.index')
            ->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function verify(Request $request, BillPayment $payment)
    {
        $this->authorize('approve', $payment);

        try {
            $this->billPaymentService->approve($payment, Auth::user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function uploadProof(Request $request, BillPayment $payment)
    {
        $this->authorize('uploadProof', $payment);

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ]);

        try {
            $this->billPaymentService->attachProof($payment, $validated['proof']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Bukti pembayaran berhasil diunggah.');
    }

    public function reject(Request $request, BillPayment $payment)
    {
        $this->authorize('reject', $payment);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->billPaymentService->reject(
                $payment,
                Auth::user(),
                $validated['rejection_reason']
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Pembayaran berhasil ditolak.');
    }
}
