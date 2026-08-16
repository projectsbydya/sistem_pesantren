<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasTenantContext;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTenantWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasTenantContext;

    /** Retry twice before marking as failed */
    public int $tries = 3;

    /** Exponential backoff: 60s, 300s, 900s */
    public array $backoff = [60, 300, 900];

    public function __construct(private readonly User $user)
    {
        // Capture tenant context at dispatch time, not at handle time
        $this->setJobTenantId($user->tenant_id);
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        // Restore tenant context — MUST be first line
        $this->bootTenantContext();

        // All model queries here are now scoped to $this->tenantId
        // e.g., Santri::all() returns only this tenant's santri

        Log::info('Sending welcome email', [
            'user_id'   => $this->user->id,
            'email'     => $this->user->email,
            'tenant_id' => $this->tenantId,
        ]);

        // Mail::to($this->user)->send(new WelcomeMail($this->user));
    }
}
