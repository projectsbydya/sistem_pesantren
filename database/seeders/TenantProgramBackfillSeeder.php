<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Backfill tenant_programs for existing tenants.
 * Idempotent - safe to run multiple times.
 * Attaches all active programs to tenants that have no programs assigned.
 */
class TenantProgramBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting tenant_programs backfill...');

        // Get all active programs (global list managed by Super Admin)
        $activePrograms = Program::where('is_active', true)->get();

        if ($activePrograms->isEmpty()) {
            $this->command->warn('No active programs found. Run ProgramSeeder first.');
            return;
        }

        $this->command->info("Found {$activePrograms->count()} active programs.");

        // Get all tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found.');
            return;
        }

        $this->command->info("Found {$tenants->count()} tenants.");

        $attachedCount = 0;
        $skippedCount = 0;

        foreach ($tenants as $tenant) {
            // Check if tenant already has any programs
            $existingCount = DB::table('tenant_programs')
                ->where('tenant_id', $tenant->id)
                ->count();

            if ($existingCount > 0) {
                $this->command->info("  - {$tenant->name}: Already has {$existingCount} programs, skipping.");
                $skippedCount++;
                continue;
            }

            // Attach all active programs to this tenant
            $now = now();
            $pivotData = [];

            foreach ($activePrograms as $program) {
                $pivotData[] = [
                    'tenant_id'    => $tenant->id,
                    'program_id'   => $program->id,
                    'is_active'    => true,
                    'activated_at' => $now,
                    'settings'     => null,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            if (!empty($pivotData)) {
                DB::table('tenant_programs')->insert($pivotData);
                $attachedCount++;
                $this->command->info("  - {$tenant->name}: Attached {$activePrograms->count()} programs.");
            }
        }

        $this->command->newLine();
        $this->command->info('=== BACKFILL SUMMARY ===');
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Tenants with programs attached', $attachedCount],
                ['Tenants skipped (already had programs)', $skippedCount],
                ['Total tenants processed', $tenants->count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('Backfill completed successfully!');
        $this->command->info('This seeder is idempotent - safe to run multiple times.');
    }
}
