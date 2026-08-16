<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\UstadzKelas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditScheduleUstadzKelas extends Command
{
    protected $signature = 'audit:schedule-ustadz-kelas
                            {--tenant= : Limit audit to a specific tenant ID}
                            {--csv : Output as CSV instead of a table}';

    protected $description = 'Audit Schedule relations to UstadzKelas assignments';

    public function handle(): int
    {
        $query = Schedule::withoutTenant();

        if ($this->option('tenant')) {
            $tenantId = (int) $this->option('tenant');
            $query->where('tenant_id', $tenantId);
        }

        $headers = [
            'schedule_id',
            'tenant_id',
            'tenant_name',
            'program_id',
            'kelas_id',
            'subject_id',
            'ustadz_id',
            'ustadz_kelas_id',
            'matching_assignment_found',
            'matched_assignment_id',
        ];

        $rows = [];

        $query->orderBy('id')->chunk(200, function ($schedules) use (&$rows) {
            foreach ($schedules as $schedule) {
                $match = $this->findMatchingUstadzKelas($schedule);

                $rows[] = [
                    $schedule->id,
                    $schedule->tenant_id,
                    $this->tenantName($schedule->tenant_id),
                    $schedule->program_id,
                    $schedule->kelas_id ?? '(null)',
                    $schedule->subject_id ?? '(null)',
                    $schedule->ustadz_id ?? '(null)',
                    $schedule->ustadz_kelas_id ?? '(null)',
                    $match ? 'yes' : 'no',
                    $match?->id ?? '(none)',
                ];
            }
        });

        if ($this->option('csv')) {
            $this->outputCsv($headers, $rows);
        } else {
            $this->table($headers, $rows);
        }

        $total = count($rows);
        $matched = count(array_filter($rows, fn ($row) => $row[8] === 'yes'));
        $orphan = $total - $matched;

        $this->newLine();
        $this->info("Total schedules: {$total}");
        $this->info("Matched: {$matched}");
        $this->warn("Orphan / unmatched: {$orphan}");

        return self::SUCCESS;
    }

    private function findMatchingUstadzKelas(Schedule $schedule): ?UstadzKelas
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

        return $query->first();
    }

    private function tenantName(int $tenantId): string
    {
        $tenant = Tenant::find($tenantId);

        return $tenant?->name ?? 'n/a';
    }

    private function outputCsv(array $headers, array $rows): void
    {
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);

        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        fclose($out);
    }
}
