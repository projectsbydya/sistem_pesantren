<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Service for validating program access within tenant context.
 * Ensures tenant-program isolation by validating program belongs to tenant.
 */
class ProgramValidationService
{
    /**
     * Get program for current tenant by slug.
     * Throws 404 if program not found or not active for tenant.
     *
     * @param string $programSlug
     * @param Tenant|null $tenant
     * @return Program
     * @throws ModelNotFoundException
     */
    public static function getProgramForTenant(string $programSlug, ?Tenant $tenant = null): Program
    {
        $tenant = $tenant ?? TenantService::getTenant();

        if (!$tenant) {
            throw new ModelNotFoundException('No tenant context available');
        }

        // Query through tenant_programs pivot for proper isolation
        $program = $tenant->activePrograms()
            ->where('slug', $programSlug)
            ->first();

        if (!$program) {
            throw (new ModelNotFoundException())->setModel(Program::class, $programSlug);
        }

        return $program;
    }

    /**
     * Check if program slug is valid for tenant (exists and active).
     *
     * @param string $programSlug
     * @param Tenant|null $tenant
     * @return bool
     */
    public static function isValidForTenant(string $programSlug, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? TenantService::getTenant();

        if (!$tenant) {
            return false;
        }

        return $tenant->activePrograms()
            ->where('slug', $programSlug)
            ->exists();
    }

    /**
     * Get first active program for tenant as fallback.
     *
     * @param Tenant|null $tenant
     * @return Program|null
     */
    public static function getFirstActiveProgram(?Tenant $tenant = null): ?Program
    {
        $tenant = $tenant ?? TenantService::getTenant();

        if (!$tenant) {
            return null;
        }

        return $tenant->activePrograms()->first();
    }

    /**
     * Resolve program slug from request with fallback to first active tenant program.
     * No hardcoded defaults.
     *
     * @param string|null $programSlug
     * @param Tenant|null $tenant
     * @return array{program: Program, slug: string}|null
     */
    public static function resolveProgram(?string $programSlug = null, ?Tenant $tenant = null): ?array
    {
        $tenant = $tenant ?? TenantService::getTenant();

        if (!$tenant) {
            return null;
        }

        // If slug provided, validate it belongs to tenant
        if ($programSlug) {
            $program = $tenant->activePrograms()
                ->where('slug', $programSlug)
                ->first();

            if ($program) {
                return ['program' => $program, 'slug' => $programSlug];
            }

            return null;
        }

        // No slug provided, use first active
        $program = $tenant->activePrograms()->first();

        if ($program) {
            return ['program' => $program, 'slug' => $program->slug];
        }

        return null;
    }
}
