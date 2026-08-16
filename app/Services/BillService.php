<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\SppExport;
use App\Models\Bill;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriProgram;
use App\Models\Ustadz;
use App\Models\User;
use App\Notifications\SppBillCreatedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

final class BillService
{
    public function indexData(User $user, array $filters): array
    {
        $bills = $this->getBills($user, $filters);

        return [
            'tenant' => TenantService::getTenant(),
            'santriList' => $this->getSantriList(),
            'kelasList' => $this->getKelasList(),
            'bills' => $bills,
            'totalUnpaid' => $this->totalUnpaid(),
            'totalPaidThisMonth' => $this->totalPaidThisMonth(),
            'countUnpaid' => $this->countUnpaid(),
            'countPaidThisMonth' => $this->countPaidThisMonth(),
            'waUrls' => $this->buildWaUrls($bills),
        ];
    }

    public function getBills(User $user, array $filters): LengthAwarePaginator
    {
        return Bill::with(['santri.parents'])
            ->currentTenant()
            ->tap(fn (Builder $query) => $this->scopeBillsForUser($query, $user))
            ->when($filters['santri_id'] ?? null, fn ($query, $santriId) => $query->where('santri_id', $santriId))
            ->when($filters['kelas_id'] ?? null, fn ($query, $kelasId) => $query->whereHas(
                'santri.programs',
                fn ($q) => $q->where('kelas_id', $kelasId)
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->orderByDesc('due_date')
            ->paginate(20)
            ->withQueryString();
    }

    public function getBillsForSantri(Santri $santri, array $filters = []): LengthAwarePaginator
    {
        return Bill::with(['santri.parents'])
            ->currentTenant()
            ->where('santri_id', $santri->id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->orderByDesc('due_date')
            ->paginate(20)
            ->withQueryString();
    }

    public function getLatestBillForSantri(Santri $santri): ?Bill
    {
        return Bill::with(['billPayments'])
            ->currentTenant()
            ->where('santri_id', $santri->id)
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->first();
    }

    private function scopeBillsForUser(Builder $query, User $user): void
    {
        if ($user->parent !== null) {
            $query->whereIn('santri_id', $user->parent->santri()->select('santri.id'));
        } elseif ($user->santri !== null) {
            $query->where('santri_id', $user->santri->id);
        }
    }

    public function getSantriList()
    {
        return Santri::currentTenant()->orderBy('name')->get();
    }

    /**
     * Build the list of Kelas, each with its enrolled santri attached as a
     * virtual 'santri' relation.
     *
     * NOTE: Class placement is tracked via the santri_program pivot
     * (SantriProgram::kelas_id), NOT the legacy santri.kelas_id column
     * (which is unused/always null). This must read from that pivot.
     */
    public function getKelasList()
    {
        $kelasList = Kelas::currentTenant()->orderBy('name')->get();

        $santriByKelas = SantriProgram::currentTenant()
            ->whereNotNull('kelas_id')
            ->whereIn('kelas_id', $kelasList->pluck('id'))
            ->with('santri')
            ->get()
            ->groupBy('kelas_id');

        return $kelasList->map(function (Kelas $kelas) use ($santriByKelas) {
            $santri = ($santriByKelas[$kelas->id] ?? collect())
                ->pluck('santri')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();

            $kelas->setRelation('santri', $santri);

            return $kelas;
        });
    }

    public function getSantriListForUser(User $user)
    {
        if ($user->parent !== null) {
            return $user->parent->santri()->orderBy('name')->get();
        }

        if ($user->santri !== null) {
            return collect([$user->santri]);
        }

        return $this->getSantriList();
    }

    public function getUnpaidBillsForUser(User $user)
    {
        $query = Bill::with(['santri'])
            ->currentTenant()
            ->where('status', '!=', 'paid')
            ->orderByDesc('due_date');

        if ($user->parent !== null) {
            $query->whereIn('santri_id', $user->parent->santri()->select('santri.id'));
        } elseif ($user->santri !== null) {
            $query->where('santri_id', $user->santri->id);
        }

        return $query->get();
    }

    public function createRules(): array
    {
        return $this->baseRules();
    }

    public function updateRules(): array
    {
        return $this->baseRules();
    }

    public function create(array $attributes): Bill
    {
        $bill = Bill::create($attributes);

        if ($bill->type === 'spp') {
            $this->notifySppBillCreated($bill);
        }

        return $bill;
    }

    /**
     * Notify the related Santri, Wali, and Orang Tua about a newly created SPP bill.
     *
     * Notification failures are isolated from the bill creation flow.
     */
    private function notifySppBillCreated(Bill $bill): void
    {
        $recipients = $this->resolveSppBillRecipients($bill);

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notification::send($recipients, new SppBillCreatedNotification($bill));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Resolve the active user accounts related to the billed Santri.
     *
     * Includes: Santri user, Wali user, and each linked Orang Tua user.
     */
    private function resolveSppBillRecipients(Bill $bill): \Illuminate\Support\Collection
    {
        $bill->load(['santri.user', 'santri.wali', 'santri.parents.user']);

        $recipients = collect();

        if ($bill->santri?->user?->is_active) {
            $recipients->push($bill->santri->user);
        }

        if ($bill->santri?->wali?->is_active) {
            $recipients->push($bill->santri->wali);
        }

        foreach ($bill->santri?->parents ?? [] as $parent) {
            if ($parent->user?->is_active) {
                $recipients->push($parent->user);
            }
        }

        return $recipients->unique('id')->values();
    }

    public function update(Bill $bill, array $attributes): Bill
    {
        $bill->update($attributes);

        return $bill->refresh();
    }

    public function delete(Bill $bill): void
    {
        $bill->delete();
    }

    public function export(array $filters): SppExport
    {
        return new SppExport(
            status: $filters['status'] ?? null,
            type: $filters['type'] ?? null,
            santriId: isset($filters['santri_id']) ? (int) $filters['santri_id'] : null,
        );
    }

    private function baseRules(): array
    {
        return [
            'santri_id' => [
                'required',
                'integer',
                Rule::exists('santri', 'id')->where('tenant_id', tenant_id()),
            ],
            'type' => ['required', 'string', Rule::in(Bill::TYPES)],
            'amount' => ['required', 'numeric', 'min:1'],
            'due_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function totalUnpaid(): float
    {
        return (float) Bill::currentTenant()->where('status', '!=', 'paid')->sum('amount');
    }

    private function totalPaidThisMonth(): float
    {
        return (float) Bill::currentTenant()
            ->where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');
    }

    private function countUnpaid(): int
    {
        return (int) Bill::currentTenant()->where('status', '!=', 'paid')->count();
    }

    private function countPaidThisMonth(): int
    {
        return (int) Bill::currentTenant()
            ->where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
    }

    private function buildWaUrls(LengthAwarePaginator $bills): array
    {
        $bendahara = Ustadz::currentTenant()
            ->where('role', Ustadz::ROLE_BENDAHARA)
            ->where('status', Ustadz::STATUS_ACTIVE)
            ->whereNotNull('phone')
            ->first();
        $urls = [];

        foreach ($bills as $bill) {
            $parent = $bill->santri?->parents?->first();
            if ($parent?->phone) {
                $urls[$bill->id] = $this->buildWaUrl($bill, $parent->phone, $bendahara);
            }
        }

        return $urls;
    }

    private function buildWaUrl(Bill $bill, string $phoneTarget, ?Ustadz $bendahara): string
    {
        $senderName = $bendahara?->user?->name ?? 'Bendahara Pesantren';
        $phoneTarget = preg_replace('/\D/', '', $phoneTarget);

        if (str_starts_with($phoneTarget, '0')) {
            $phoneTarget = '62'.substr($phoneTarget, 1);
        }

        $message = urlencode(
            "Assalamu'alaikum, Yth. Orang Tua/Wali dari *".($bill->santri?->name ?? '-')."*,\n\n"
            ."Berikut informasi tagihan:\n"
            ."Jenis      : *".(Bill::TYPE_LABELS[$bill->type] ?? $bill->type)."*\n"
            ."Jumlah     : *Rp ".number_format((float) $bill->amount, 0, ',', '.')."*\n"
            ."Jatuh Tempo: *".($bill->due_date?->format('d/m/Y') ?? '-')."*\n"
            ."Status     : *".(Bill::STATUS_LABELS[$bill->status] ?? $bill->status)."*\n\n"
            ."Mohon segera melakukan pembayaran. Jazakallahu khairan.\n"
            ."_{$senderName}_"
        );

        return "https://wa.me/{$phoneTarget}?text={$message}";
    }
}
