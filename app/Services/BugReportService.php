<?php

namespace App\Services;

use App\Models\BugReport;
use App\Models\BugReportAttachment;
use App\Models\User;
use App\Notifications\BugReportSubmitted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Core bug/error report creation service.
 *
 * All tenant and reporter identity is derived from the authenticated context
 * (TenantService / auth()->user()), never from request input. The service only
 * accepts report content and context data (title, description, category,
 * severity, source URL, metadata).
 */
class BugReportService
{
    /**
     * Create a new bug report for the current tenant and authenticated user.
     *
     * An optional screenshot is attached after the report is created. If
     * attachment validation fails, the partially created report is removed so
     * the submission is rejected as a whole.
     *
     * @param  array<string, mixed> $data Report content/context data.
     * @param  User|null $user Reporter override for tests/console callers.
     * @param  UploadedFile|null $screenshot Optional screenshot image.
     * @throws ValidationException
     */
    public function createBugReport(array $data, ?User $user = null, ?UploadedFile $screenshot = null): BugReport
    {
        $user ??= Auth::user();

        $validated = $this->validateBugReportData($data);

        // Identity is always taken from the authenticated context.
        $validated['reporter_id'] = $user?->id;
        $validated['tenant_id'] = tenant_id();
        $validated['status'] = BugReport::STATUS_OPEN;

        $bugReport = BugReport::create($validated);

        if ($screenshot !== null && $screenshot->isValid()) {
            try {
                BugReportAttachment::attachScreenshot($bugReport, $user, $screenshot);
            } catch (ValidationException $e) {
                $bugReport->delete();
                throw $e;
            }
        }

        // Notify all active Super Admins via the in-app database channel.
        // Queueing keeps notification failures from affecting the saved report.
        $recipients = app(NotificationRecipientService::class)->superAdmins();

        if ($recipients->isNotEmpty()) {
            try {
                Notification::send($recipients, new BugReportSubmitted($bugReport));
            } catch (\Throwable $e) {
                // Notification failure must never roll back or delete the bug report.
                report($e);
            }
        }

        return $bugReport;
    }

    /**
     * Validate only the fields that may come from the caller.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     * @throws ValidationException
     */
    private function validateBugReportData(array $data): array
    {
        return Validator::make($data, [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category'    => ['required', 'string', Rule::in(BugReport::$categories)],
            'severity'    => ['required', 'string', Rule::in(BugReport::$severities)],
            'source_url'  => ['nullable', 'url', 'max:500'],
            'metadata'    => ['nullable', 'array'],
        ])->validate();
    }
}
