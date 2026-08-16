<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BugReport;
use App\Services\BugReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Bug report submission controller for tenant users.
 *
 * Only the store action is exposed to tenants. Listing/detail access is
 * intentionally denied to tenant users by BugReportPolicy and is reserved
 * for the super-admin support dashboard.
 */
final class BugReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private BugReportService $bugReportService) {}

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BugReport::class);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category'    => ['required', 'string', Rule::in(BugReport::$categories)],
            'severity'    => ['required', 'string', Rule::in(BugReport::$severities)],
            'source_url'  => ['nullable', 'url', 'max:500'],
            'metadata'    => ['nullable', 'array'],
            'screenshot'  => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:5120'],
        ]);

        $screenshot = $validated['screenshot'] ?? null;
        unset($validated['screenshot']);

        // Identity fields (tenant_id, reporter_id, status) are supplied by the
        // service from the authenticated context, never from request input.
        $this->bugReportService->createBugReport($validated, $request->user(), $screenshot);

        return redirect()->back()->with('success', 'Laporan bug berhasil dikirim.');
    }
}
