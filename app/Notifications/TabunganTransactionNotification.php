<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tabungan;
use Illuminate\Notifications\Notification;

/**
 * In-app notification sent to the related Santri, Wali, and Orang Tua
 * when a new Tabungan transaction (setor / tarik) is recorded.
 *
 * Uses only the database channel so the notification appears in the
 * notification bell UI. Delivered synchronously to avoid depending on a
 * queue worker for immediate feedback.
 */
class TabunganTransactionNotification extends Notification
{
    public function __construct(public readonly Tabungan $tabungan)
    {
    }

    /**
     * Deliver only via the in-app database channel.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Build the database payload for the in-app notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $tabungan = $this->tabungan->fresh(['santri']);
        $santri = $tabungan->santri;

        $santriName = $santri?->name ?? 'Santri';
        $jenisLabel = Tabungan::JENIS_LABELS[$tabungan->jenis] ?? ucfirst($tabungan->jenis);
        $amount = 'Rp ' . number_format((float) $tabungan->jumlah, 0, ',', '.');
        $date = $tabungan->tanggal?->format('d M Y') ?? '-';

        $saldo = Tabungan::where('santri_id', $tabungan->santri_id)
            ->setor()
            ->sum('jumlah')
            - Tabungan::where('santri_id', $tabungan->santri_id)
            ->tarik()
            ->sum('jumlah');
        $saldoFormatted = 'Rp ' . number_format((float) $saldo, 0, ',', '.');

        $title = $tabungan->jenis === 'setor'
            ? "[Setoran Tabungan] {$santriName}"
            : "[Penarikan Tabungan] {$santriName}";

        $message = "{$jenisLabel} sebesar {$amount} untuk {$santriName} pada tanggal {$date}. Saldo saat ini: {$saldoFormatted}.";

        return [
            'type'         => 'tabungan_transaction',
            'title'        => $title,
            'message'      => $message,
            'action_url'   => tenant_route('dashboard.tabungan.santri', ['santri' => $tabungan->santri_id], true),
            'tabungan_id'  => $tabungan->id,
            'santri_id'    => $tabungan->santri_id,
            'tenant_id'    => $tabungan->tenant_id,
            'jenis'        => $tabungan->jenis,
            'jumlah'       => $tabungan->jumlah,
            'saldo'        => $saldo,
            'tanggal'      => $tabungan->tanggal?->toDateString(),
            'created_at'   => $tabungan->created_at?->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'tabungan_transaction',
            'tabungan_id' => $this->tabungan->id,
            'santri_id'   => $this->tabungan->santri_id,
            'tenant_id'   => $this->tabungan->tenant_id,
            'jenis'       => $this->tabungan->jenis,
            'jumlah'      => $this->tabungan->jumlah,
        ];
    }
}
