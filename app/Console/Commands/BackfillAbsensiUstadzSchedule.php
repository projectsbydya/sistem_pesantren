<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillAbsensiUstadzSchedule extends Command
{
    protected $signature = 'backfill:absensi-ustadz-schedule
                            {--dry-run : Report mappings without updating attendance}
                            {--tenant= : Limit processing to one tenant ID}';

    protected $description = 'Backfill absensi_ustadz.schedule_id from unambiguous historical schedule matches';

    public function handle(): int
    {
        if (! Schema::hasColumn('absensi_ustadz', 'schedule_id')) {
            $this->error('Column absensi_ustadz.schedule_id does not exist. Run migrations first.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $tenantId = $this->option('tenant');
        $mapped = 0;
        $noMatch = 0;
        $ambiguous = 0;

        $query = DB::table('absensi_ustadz')
            ->whereNull('schedule_id')
            ->when($tenantId !== null, fn ($builder) => $builder->where('tenant_id', (int) $tenantId));

        $query->orderBy('id')->chunkById(200, function ($records) use (
            $dryRun,
            &$mapped,
            &$noMatch,
            &$ambiguous
        ) {
            foreach ($records as $record) {
                $candidateIds = $this->candidateScheduleIds($record);

                if ($candidateIds->count() === 1) {
                    if (! $dryRun) {
                        DB::table('absensi_ustadz')
                            ->where('id', $record->id)
                            ->whereNull('schedule_id')
                            ->update(['schedule_id' => $candidateIds->first()]);
                    }

                    $mapped++;

                    continue;
                }

                $reason = $candidateIds->isEmpty() ? 'no_match' : 'ambiguous';
                $reason === 'no_match' ? $noMatch++ : $ambiguous++;

                $this->warn(json_encode([
                    'attendance_id' => $record->id,
                    'tenant_id' => $record->tenant_id,
                    'ustadz_id' => $record->ustadz_id,
                    'tanggal' => $record->tanggal,
                    'reason' => $reason,
                    'candidate_schedule_ids' => $candidateIds->all(),
                ], JSON_UNESCAPED_SLASHES));
            }
        });

        $this->newLine();
        $this->info('Mapped: '.$mapped.($dryRun ? ' (dry-run)' : ''));
        $this->warn('Unmapped (no match): '.$noMatch);
        $this->warn('Unmapped (ambiguous): '.$ambiguous);

        return self::SUCCESS;
    }

    private function candidateScheduleIds(object $record)
    {
        $day = Schedule::HARI[CarbonImmutable::parse($record->tanggal)->dayOfWeekIso - 1];

        return DB::table('jadwal as schedules')
            ->join('ustadz_kelas as assignments', 'assignments.id', '=', 'schedules.ustadz_kelas_id')
            ->where('schedules.tenant_id', $record->tenant_id)
            ->where('assignments.tenant_id', $record->tenant_id)
            ->where('assignments.ustadz_id', $record->ustadz_id)
            ->where('schedules.hari', $day)
            ->whereColumn('assignments.program_id', 'schedules.program_id')
            ->whereColumn('assignments.kelas_id', 'schedules.kelas_id')
            ->whereColumn('assignments.subject_id', 'schedules.subject_id')
            ->distinct()
            ->pluck('schedules.id');
    }
}
