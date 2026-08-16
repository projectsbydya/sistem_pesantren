<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BugReport;

/**
 * Super Admin Bug Report detail controller.
 *
 * Provides cross-tenant read access to bug reports submitted by tenant users.
 * All access is gated by BugReportPolicy and the super_admin.gate route middleware.
 */
class BugReportController extends Controller
{
    /**
     * Display the specified bug report across tenants.
     */
    public function show(BugReport $bugReport)
    {
        $this->authorize('view', $bugReport);

        $bugReport->load(['tenant', 'reporter', 'attachments']);

        return view('dashboard.super-admin.bug-reports.show', compact('bugReport'));
    }
}
