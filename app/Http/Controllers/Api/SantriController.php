<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SantriController extends Controller
{
    /**
     * List santri — automatically filtered by current tenant via TenantScope.
     * Returns empty array if no tenant context.
     */
    public function index(): JsonResponse
    {
        return response()->json(Santri::all());
    }

    /**
     * List all santri across all tenants — super admin only.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function all(): JsonResponse
    {
        Gate::authorize('access-super-admin-panel');

        return response()->json(
            Santri::withoutTenant()->with('tenant')->get()
        );
    }
}
