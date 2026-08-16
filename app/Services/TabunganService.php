<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tabungan;
use App\Notifications\TabunganTransactionNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

final class TabunganService
{
    public function create(array $attributes): Tabungan
    {
        $tabungan = Tabungan::create($attributes);

        $this->notifyTransactionCreated($tabungan);

        return $tabungan;
    }

    /**
     * Notify the related Santri, Wali, and Orang Tua about a newly recorded
     * Tabungan transaction (setor or tarik).
     *
     * Notification failures are isolated from the transaction creation flow.
     */
    private function notifyTransactionCreated(Tabungan $tabungan): void
    {
        $recipients = $this->resolveRecipients($tabungan);

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notification::send($recipients, new TabunganTransactionNotification($tabungan));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Resolve the active user accounts related to the transaction's Santri.
     *
     * Includes: Santri user, Wali user, and each linked Orang Tua user.
     * Duplicates are removed and only active users are returned.
     */
    private function resolveRecipients(Tabungan $tabungan): Collection
    {
        $tabungan->load(['santri.user', 'santri.wali', 'santri.parents.user']);

        $recipients = collect();

        if ($tabungan->santri?->user?->is_active) {
            $recipients->push($tabungan->santri->user);
        }

        if ($tabungan->santri?->wali?->is_active) {
            $recipients->push($tabungan->santri->wali);
        }

        foreach ($tabungan->santri?->parents ?? [] as $parent) {
            if ($parent->user?->is_active) {
                $recipients->push($parent->user);
            }
        }

        return $recipients->unique('id')->values();
    }
}
