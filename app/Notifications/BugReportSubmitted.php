<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\BugReport;
use Illuminate\Notifications\Notification;

/**
 * In-app notification sent to all active Super Admins when a tenant user
 * submits a bug/error report.
 *
 * This notification uses only the database channel and is delivered
 * synchronously so it appears immediately without requiring a dedicated
 * queue worker. Notification failures are isolated from the BugReport
 * persistence flow by the try/catch in BugReportService.
 */
class BugReportSubmitted extends Notification
{
    use HasTenantContext;

    public function __construct(public readonly BugReport $bugReport)
    {
        $this->setJobTenantId($bugReport->tenant_id);
    }

    /**
     * Deliver only via the in-app database channel.
     */
    public function via(object $notifiable): array
    {
        $this->bootTenantContext();

        return ['database'];
    }

    /**
     * Build the database payload for the in-app notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $bugReport = $this->bugReport->fresh(['reporter', 'tenant']);

        $reporter = $bugReport->reporter?->name ?? 'Tidak diketahui';
        $tenant   = $bugReport->tenant?->name ?? 'Tidak diketahui';

        return [
            'type'          => 'bug_report_submitted',
            'title'         => "[Laporan Bug] {$bugReport->title}",
            'message'       => "Laporan bug baru dari {$reporter} di tenant {$tenant}.",
            'bug_report_id' => $bugReport->id,
            'tenant_id'     => $bugReport->tenant_id,
            'severity'      => $bugReport->severity,
            'action_url'    => rtrim(config('app.url'), '/') . route('dashboard.super-admin.bug-reports.show', $bugReport, false),
            'reporter_name' => $reporter,
            'tenant_name'   => $tenant,
            'category'      => $bugReport->category,
            'created_at'    => $bugReport->created_at?->toDateTimeString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'bug_report_submitted',
            'title'         => $this->bugReport->title,
            'bug_report_id' => $this->bugReport->id,
            'tenant_id'     => $this->bugReport->tenant_id,
            'severity'      => $this->bugReport->severity,
        ];
    }
}
