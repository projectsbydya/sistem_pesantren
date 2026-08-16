<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

final class BillPaymentService
{
    public function getPaymentsFor(User $user, ?string $status = null): LengthAwarePaginator
    {
        $query = BillPayment::with(['santri', 'bill', 'verifiedBy'])
            ->currentTenant()
            ->orderByDesc('submitted_at');

        if ($user->parent !== null) {
            $query->whereIn('santri_id', $user->parent->santri()->select('santri.id'));
        } elseif ($user->santri !== null) {
            $query->where('santri_id', $user->santri->id);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function getPendingPayments(User $user): LengthAwarePaginator
    {
        $query = BillPayment::with(['santri', 'bill', 'verifiedBy'])
            ->currentTenant()
            ->where('status', 'pending')
            ->orderByDesc('submitted_at');

        if ($user->parent !== null) {
            $query->whereIn('santri_id', $user->parent->santri()->select('santri.id'));
        } elseif ($user->santri !== null) {
            $query->where('santri_id', $user->santri->id);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function createRules(): array
    {
        return [
            'bill_id' => [
                'required',
                'integer',
                Rule::exists('bills', 'id')->where('tenant_id', tenant_id()),
            ],
            'santri_id' => [
                'required',
                'integer',
                Rule::exists('santri', 'id')->where('tenant_id', tenant_id()),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'string', Rule::in(BillPayment::METHODS)],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'transfer_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function updateRules(): array
    {
        return $this->createRules();
    }

    public function findBill(int $billId): Bill
    {
        return Bill::currentTenant()->findOrFail($billId);
    }

    public function create(Bill $bill, array $attributes): BillPayment
    {
        return DB::transaction(function () use ($bill, $attributes) {
            $this->assertMatchesBill($bill, $attributes);
            $this->assertOutstanding($bill, (float) $attributes['amount']);

            $payment = $bill->billPayments()->create([
                ...$attributes,
                'tenant_id' => $bill->tenant_id,
                'santri_id' => $bill->santri_id,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $this->syncBill($bill);

            return $payment;
        });
    }

    public function update(BillPayment $payment, array $attributes): BillPayment
    {
        return DB::transaction(function () use ($payment, $attributes) {
            if (! $payment->isPending()) {
                throw new RuntimeException('Pembayaran yang sudah diproses tidak dapat diubah.');
            }

            $originalBill = $payment->bill;
            $bill = $this->findBill((int) $attributes['bill_id']);
            $this->assertMatchesBill($bill, $attributes);
            $this->assertOutstanding($bill, (float) $attributes['amount'], $payment);

            $payment->update([
                ...$attributes,
                'tenant_id' => $bill->tenant_id,
                'santri_id' => $bill->santri_id,
                'submitted_at' => now(),
            ]);

            $this->syncBill($originalBill);
            if ($originalBill->id !== $bill->id) {
                $this->syncBill($bill);
            }

            return $payment->refresh();
        });
    }

    public function delete(BillPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if (! $payment->isPending()) {
                throw new RuntimeException('Pembayaran yang sudah diproses tidak dapat dihapus.');
            }

            $bill = $payment->bill;
            $payment->delete();
            $this->syncBill($bill);
        });
    }

    public function approve(BillPayment $payment, User $verifier): BillPayment
    {
        return DB::transaction(function () use ($payment, $verifier) {
            if (! $payment->isPending()) {
                throw new RuntimeException('Pembayaran ini sudah diproses.');
            }

            $payment->update([
                'status' => 'approved',
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);
            $this->syncBill($payment->bill);

            return $payment->refresh();
        });
    }

    public function reject(BillPayment $payment, User $verifier, string $reason): BillPayment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason) {
            if (! $payment->isPending()) {
                throw new RuntimeException('Pembayaran ini sudah diproses.');
            }

            $payment->update([
                'status' => 'rejected',
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);
            $this->syncBill($payment->bill);

            return $payment->refresh();
        });
    }

    public function attachProof(BillPayment $payment, UploadedFile $file): BillPayment
    {
        return DB::transaction(function () use ($payment, $file) {
            if (! $payment->isManual()) {
                throw new RuntimeException('Bukti hanya dapat ditambahkan untuk pembayaran manual.');
            }

            $path = $file->store('transfer-proofs', 'public');

            if ($payment->transfer_proof) {
                Storage::disk('public')->delete($payment->transfer_proof);
            }

            $payment->update(['transfer_proof' => $path]);

            return $payment->refresh();
        });
    }

    private function assertMatchesBill(Bill $bill, array $attributes): void
    {
        if ((int) $attributes['santri_id'] !== (int) $bill->santri_id) {
            throw new RuntimeException('Santri pembayaran harus sesuai dengan tagihan.');
        }

        if ($bill->status === 'paid') {
            throw new RuntimeException('Tagihan sudah lunas.');
        }
    }

    private function assertOutstanding(Bill $bill, float $amount, ?BillPayment $excludedPayment = null): void
    {
        $approvedAmount = (float) $bill->billPayments()
            ->where('status', 'approved')
            ->sum('amount');
        $pendingAmount = (float) $bill->billPayments()
            ->where('status', 'pending')
            ->when($excludedPayment !== null, fn ($query) => $query->where('id', '!=', $excludedPayment->id))
            ->sum('amount');

        if ($amount + $approvedAmount + $pendingAmount > (float) $bill->amount) {
            throw new RuntimeException('Jumlah pembayaran melebihi sisa tagihan.');
        }
    }

    private function syncBill(Bill $bill): void
    {
        $approvedAmount = (float) $bill->billPayments()
            ->where('status', 'approved')
            ->sum('amount');
        $hasPendingPayment = $bill->billPayments()
            ->where('status', 'pending')
            ->exists();

        $bill->update([
            'paid_amount' => $approvedAmount,
            'status' => $approvedAmount >= (float) $bill->amount
                ? 'paid'
                : ($hasPendingPayment ? 'pending' : 'unpaid'),
        ]);
    }
}
