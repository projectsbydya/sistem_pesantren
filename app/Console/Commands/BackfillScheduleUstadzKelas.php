<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\UstadzKelas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillScheduleUstadzKelas extends Command
{
    protected $signature = 'backfill:schedule-ustadz-kelas
                            {--dry-run : Show what would be updated without changing data}
                            {--tenant= : Limit backfill to a specific tenant ID}';

    protected $description = 'Backfill jadwal.ustadz_kelas_id and ustadz_id from UstadzKelas matches';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $query = Schedule::withoutTenant()
            ->whereNull('ustadz_kelas_id');

        if ($this->option('tenant')) {
            $query->where('tenant_id', (int) $this->option('tenant'));
        }

        $updated = 0;
        $skippedNoMatch = 0;
        $skippedAmbiguous = 0;
        $rows = [];

        $query->orderBy('id')->chunk(200, function ($schedules) use ($dryRun, &$updated, &$skippedNoMatch, &$skippedAmbiguous, &$rows) {
            foreach ($schedules as $schedule) {
                $matches = $this->findMatches($schedule);

                if ($matches->count() === 1) {
                    $assignment = $matches->first();

                    $rows[] = [
                        $schedule->id,
                        $schedule->tenant_id,
                        $assignment->id,
                        $assignment->ustadz_id,
                        $dryRun ? 'would update' : 'updated',
                    ];

                    if (!$dryRun) {
                        DB::table('jadwal')
                            ->where('id', $schedule->id)
                            ->update([
                                'ustadz_kelas_id' => $assignment->id,
                                'ustadz_id'       => $assignment->ustadz_id,
                            ]);
                    }

                    $updated++;
                } elseif ($matches->count() === 0) {
                    $rows[] = [
                        $schedule->id,
                        $schedule->tenant_id,
                        '(none)',
                        '(none)',
                        'skipped: no match',
                    ];
                    $skippedNoMatch++;
                } else {
                    $rows[] = [
                        $schedule->id,
                        $schedule->tenant_id,
                        implode(',', $matches->pluck('id')->toArray()),
                        '(multiple)',
                        'skipped: ambiguous',
                    ];
                    $skippedAmbiguous++;
                }
            }
        });

        $this->table(
            ['schedule_id', 'tenant_id', 'matched_ustadz_kelas_id', 'matched_ustadz_id', 'status'],
            $rows
        );

        $this->newLine();
        $this->info('Total candidates: ' . ($updated + $skippedNoMatch + $skippedAmbiguous));
        $this->info('Updated: ' . $updated . ($dryRun ? ' (dry-run)' : ''));
        $this->warn('Skipped (no match): ' . $skippedNoMatch);
        $this->warn('Skipped (ambiguous): ' . $skippedAmbiguous);

        return self::SUCCESS;
    }

    private function findMatches(Schedule $schedule)
    {
        $query = UstadzKelas::withoutTenant()
            ->where('tenant_id', $schedule->tenant_id)
            ->where('program_id', $schedule->program_id)
            ->where('kelas_id', $schedule->kelas_id);

        if ($schedule->subject_id) {
            $query->where('subject_id', $schedule->subject_id);
        }

        if ($schedule->ustadz_id) {
            $query->where('ustadz_id', $schedule->ustadz_id);
        }

        return $query->get(['id', 'ustadz_id']);
    }
}
