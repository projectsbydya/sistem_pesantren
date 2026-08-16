<?php

namespace App\Services;

use App\Models\Program;
use App\Models\TenantProgram;
use Illuminate\Support\Facades\Cache;

class ProgramAccessService
{
    public static function accessiblePrograms(?int $tenantId = null)
    {
        $tenantId = $tenantId ?? tenant_id();
        if ($tenantId === null) {
            return collect();
        }
        return Cache::remember("tenant_programs:{$tenantId}", 300, function () use ($tenantId) {
            return Program::whereHas('tenants', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)
                      ->where('tenant_programs.is_active', true);
            })->get();
        });
    }

    public static function accessibleProgramIds(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        if ($tenantId === null) {
            return [];
        }
        return Cache::remember("tenant_program_ids:{$tenantId}", 300, function () use ($tenantId) {
            return TenantProgram::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->pluck('program_id')
                ->toArray();
        });
    }

    public static function canAccess(int $programId, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        if ($tenantId === null) {
            return false;
        }
        $user = auth()->user();
        if ($user && $user->isSuperAdmin()) {
            return true;
        }
        return TenantProgram::where('tenant_id', $tenantId)
            ->where('program_id', $programId)
            ->where('is_active', true)
            ->exists();
    }

    public static function getBySlug(string $programSlug, ?int $tenantId = null): ?Program
    {
        $tenantId = $tenantId ?? tenant_id();
        $program = Program::bySlug($programSlug)->first();
        if (!$program) {
            return null;
        }
        if (!self::canAccess($program->id, $tenantId)) {
            abort(403, 'Anda tidak memiliki akses ke program ini.');
        }
        return $program;
    }

    public static function scopeQuery($query, ?int $tenantId = null)
    {
        $tenantId = $tenantId ?? tenant_id();
        $programIds = self::accessibleProgramIds($tenantId);
        if (empty($programIds)) {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereIn('program_id', $programIds);
    }
}
