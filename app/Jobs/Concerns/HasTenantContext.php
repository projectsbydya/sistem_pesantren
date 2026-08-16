<?php

namespace App\Jobs\Concerns;

use App\Services\TenantService;

/**
 * Trait for queue jobs that must operate within a specific tenant context.
 *
 * Usage in a job:
 *
 *   class SendWelcomeEmail implements ShouldQueue
 *   {
 *       use HasTenantContext;
 *
 *       public function __construct(int $tenantId, private User $user)
 *       {
 *           $this->setJobTenantId($tenantId);  // stores $this->tenantId
 *       }
 *
 *       public function handle(): void
 *       {
 *           $this->bootTenantContext();  // restores TenantService state for this job
 *           // Now TenantScope + all models work correctly
 *           Mail::to($this->user)->send(new WelcomeMail());
 *       }
 *   }
 *
 * Why this is necessary:
 *   Queue workers are long-running processes. TenantService uses static properties
 *   for performance (avoids a DB hit per query). Between jobs, static state is
 *   cleared automatically by bootTenantContext(). Without this trait, a worker
 *   processing Job A then Job B would retain Job A's tenant context in Job B.
 */
trait HasTenantContext
{
    public int $tenantId;

    protected function setJobTenantId(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Restore tenant context at the start of handle().
     * MUST be called as the first line of handle().
     */
    protected function bootTenantContext(): void
    {
        TenantService::forJob($this->tenantId);
    }

    /**
     * Called automatically when the job fails.
     * Clears tenant state to prevent bleed into retry attempts.
     */
    public function failed(\Throwable $exception): void
    {
        TenantService::clear();
    }
}
