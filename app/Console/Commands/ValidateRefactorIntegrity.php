<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateRefactorIntegrity extends Command
{
    protected $signature   = 'refactor:validate-integrity {--fix : Auto-attempt backfill where a matching ustadz_kelas row exists}';
    protected $description = 'Validate ustadz_kelas_id integrity across all refactored tables';

    // Tables that must have ustadz_kelas_id after the refactor migration
    private const USTADZ_KELAS_TABLES = [
        'nilai',
        'elearning',
        'jadwal',
        'absensi_santri',
        'hafalan_nilai',
    ];

    // Tables that still carry ustadz_id as an authorship/direct FK (not access control)
    private const USTADZ_DIRECT_TABLES = [
        'hafalan_quran',
        'hafalan_kitab',
    ];

    public function handle(): int
    {
        $this->info('');
        $this->info('━━━  Refactor Integrity Validator  ━━━');
        $totalIssues = 0;

        // ─── CHECK 1: ustadz_kelas_id IS NULL on refactored tables ───────────
        $this->info('');
        $this->line('<fg=yellow>[CHECK 1]</> <options=bold>ustadz_kelas_id IS NULL</>');
        $this->line('  Tables: ' . implode(', ', self::USTADZ_KELAS_TABLES));

        foreach (self::USTADZ_KELAS_TABLES as $table) {
            if (! $this->tableHasColumn($table, 'ustadz_kelas_id')) {
                $this->warn("  SKIP   {$table} — column ustadz_kelas_id not found (migration pending?)");
                continue;
            }

            $count = DB::table($table)->whereNull('ustadz_kelas_id')->count();

            if ($count === 0) {
                $this->line("  <fg=green>OK</>     {$table} — 0 NULL rows");
            } else {
                $this->line("  <fg=red>FAIL</>   {$table} — {$count} rows with ustadz_kelas_id IS NULL");
                $totalIssues += $count;

                if ($this->option('fix')) {
                    $fixed = $this->backfillUstadzKelasId($table);
                    $this->line("         backfilled {$fixed}/{$count} rows");
                }
            }
        }

        // ─── CHECK 2: ustadz_kelas cross-tenant mismatch ─────────────────────
        $this->info('');
        $this->line('<fg=yellow>[CHECK 2]</> <options=bold>ustadz_kelas.tenant_id ≠ record.tenant_id</>');

        foreach (self::USTADZ_KELAS_TABLES as $table) {
            if (! $this->tableHasColumn($table, 'ustadz_kelas_id')) {
                continue;
            }

            $count = DB::table($table)
                ->join('ustadz_kelas', "{$table}.ustadz_kelas_id", '=', 'ustadz_kelas.id')
                ->whereColumn("{$table}.tenant_id", '!=', 'ustadz_kelas.tenant_id')
                ->count();

            if ($count === 0) {
                $this->line("  <fg=green>OK</>     {$table} — no cross-tenant mismatches");
            } else {
                $this->line("  <fg=red>FAIL</>   {$table} — {$count} rows with mismatched tenant_id");
                $totalIssues += $count;
            }
        }

        // ─── CHECK 3: orphaned ustadz_kelas_id (FK points to deleted row) ────
        $this->info('');
        $this->line('<fg=yellow>[CHECK 3]</> <options=bold>ustadz_kelas_id points to non-existent row</>');

        foreach (self::USTADZ_KELAS_TABLES as $table) {
            if (! $this->tableHasColumn($table, 'ustadz_kelas_id')) {
                continue;
            }

            $count = DB::table($table)
                ->whereNotNull('ustadz_kelas_id')
                ->whereNotExists(function ($q) use ($table) {
                    $q->select(DB::raw(1))
                      ->from('ustadz_kelas')
                      ->whereColumn('ustadz_kelas.id', "{$table}.ustadz_kelas_id");
                })
                ->count();

            if ($count === 0) {
                $this->line("  <fg=green>OK</>     {$table} — no orphaned FK rows");
            } else {
                $this->line("  <fg=red>FAIL</>   {$table} — {$count} rows with orphaned ustadz_kelas_id");
                $totalIssues += $count;
            }
        }

        // ─── CHECK 4: hafalan_quran/kitab — ustadz_id points to non-existent ustadz ───
        $this->info('');
        $this->line('<fg=yellow>[CHECK 4]</> <options=bold>hafalan_quran/kitab — orphaned ustadz_id</>');

        foreach (self::USTADZ_DIRECT_TABLES as $table) {
            if (! $this->tableHasColumn($table, 'ustadz_id')) {
                continue;
            }

            $count = DB::table($table)
                ->whereNotNull('ustadz_id')
                ->whereNotExists(function ($q) use ($table) {
                    $q->select(DB::raw(1))
                      ->from('ustadz')
                      ->whereColumn('ustadz.id', "{$table}.ustadz_id");
                })
                ->count();

            if ($count === 0) {
                $this->line("  <fg=green>OK</>     {$table} — no orphaned ustadz_id rows");
            } else {
                $this->line("  <fg=red>FAIL</>   {$table} — {$count} rows with orphaned ustadz_id");
                $totalIssues += $count;
            }
        }

        // ─── CHECK 5: ustadz_kelas.ustadz_id user_id mismatch ────────────────
        // Detects rows where ustadz_kelas.ustadz_id points to an ustadz whose
        // user_id doesn't match the users table (broken provisioning).
        $this->info('');
        $this->line('<fg=yellow>[CHECK 5]</> <options=bold>ustadz_kelas — ustadz has no linked user</>');

        $count = DB::table('ustadz_kelas')
            ->join('ustadz', 'ustadz_kelas.ustadz_id', '=', 'ustadz.id')
            ->whereNull('ustadz.user_id')
            ->count();

        if ($count === 0) {
            $this->line("  <fg=green>OK</>     ustadz_kelas — all ustadz have linked users");
        } else {
            $this->line("  <fg=red>FAIL</>   ustadz_kelas — {$count} rows where ustadz.user_id IS NULL");
            $totalIssues += $count;
        }

        // ─── CHECK 6: jadwal — kelas_id IS NULL (required for policy resolution) ─
        $this->info('');
        $this->line('<fg=yellow>[CHECK 6]</> <options=bold>jadwal.kelas_id IS NULL (breaks policy)</>');

        if ($this->tableHasColumn('jadwal', 'kelas_id')) {
            $count = DB::table('jadwal')
                ->whereNotNull('ustadz_kelas_id') // only care about migrated rows
                ->whereNull('kelas_id')
                ->count();

            if ($count === 0) {
                $this->line("  <fg=green>OK</>     jadwal — all migrated rows have kelas_id");
            } else {
                $this->line("  <fg=red>FAIL</>   jadwal — {$count} rows with ustadz_kelas_id set but kelas_id NULL");
                $totalIssues += $count;
            }
        }

        // ─── SUMMARY ─────────────────────────────────────────────────────────
        $this->info('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($totalIssues === 0) {
            $this->info('✓  All checks passed — refactor integrity confirmed.');
            return self::SUCCESS;
        }

        $this->error("✗  {$totalIssues} issue(s) found. Run with --fix to attempt backfill.");
        return self::FAILURE;
    }

    // -------------------------------------------------------------------------

    /**
     * Attempt to backfill ustadz_kelas_id from existing ustadz_kelas rows
     * matched by (tenant_id, kelas_id, subject_id, program_id) where available.
     */
    private function backfillUstadzKelasId(string $table): int
    {
        $fixed = 0;

        $rows = DB::table($table)
            ->whereNull('ustadz_kelas_id')
            ->get();

        foreach ($rows as $row) {
            $query = DB::table('ustadz_kelas')
                ->where('tenant_id', $row->tenant_id);

            if (isset($row->kelas_id) && $row->kelas_id) {
                $query->where('kelas_id', $row->kelas_id);
            }

            if (isset($row->subject_id) && $row->subject_id) {
                $query->where('subject_id', $row->subject_id);
            }

            // Use program_id instead of legacy program enum
            if (isset($row->program_id) && $row->program_id) {
                $query->where('program_id', $row->program_id);
            }

            $match = $query->first();

            if ($match) {
                DB::table($table)->where('id', $row->id)->update([
                    'ustadz_kelas_id' => $match->id,
                ]);
                $fixed++;
            }
        }

        return $fixed;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return in_array(
            $column,
            DB::getSchemaBuilder()->getColumnListing($table)
        );
    }
}
