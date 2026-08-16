<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrate student-centric Modern/Diniyah records into the class-centric
 * assignments + assignment_members schema.
 *
 * Grouping strategy: legacy tables stored one row PER STUDENT with no shared
 * "assignment" identity. To reconstruct a shared Assignment, rows are grouped
 * by their BUSINESS KEY — tenant_id + program_id + kelas_id + type + title +
 * target — since those fields describe what the assignment IS (the same
 * hafalan target / vocabulary word / muhadhoroh title assigned to a class).
 * Secondary/descriptive attributes (language, category, theme_id, etc.) are
 * NOT part of the grouping key — they ride along as metadata on whichever
 * row is encountered first for that business key. A hash of the key tuple is
 * only used as an in-memory lookup for "have we created this assignment yet",
 * it is not itself the identity.
 *
 * Every row is scoped by its own tenant_id/program_id (copied verbatim from
 * the source row), so cross-tenant/cross-program leakage cannot occur even
 * though this migration queries via DB:: (bypassing Eloquent's TenantScope).
 *
 * Old tables are left untouched so the migration is reversible and auditable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assignments') || ! Schema::hasTable('assignment_members')) {
            return;
        }

        $this->migrateDiniyahHafalan();
        $this->migrateVocabulary();
        $this->migrateMuhadatsah();
        $this->migrateMuhadhoroh();
    }

    public function down(): void
    {
        DB::table('assignment_members')->delete();
        DB::table('assignments')->delete();
    }

    // =====================================================================
    // Diniyah Hafalan
    // =====================================================================

    private function migrateDiniyahHafalan(): void
    {
        if (! Schema::hasTable('diniyah_hafalans')) {
            return;
        }

        $rows = DB::table('diniyah_hafalans')->orderBy('id')->get();
        $map = [];

        foreach ($rows as $row) {
            $kelasId = $this->resolveKelasId($row->tenant_id, $row->santri_id, $row->program_id);
            $type = "diniyah-hafalan-{$row->type}";

            $assignmentId = $this->firstOrCreateAssignment($map, [
                $row->tenant_id, $row->program_id, $kelasId, $type, $row->title, $row->target,
            ], [
                'tenant_id'    => $row->tenant_id,
                'program_id'   => $row->program_id,
                'kelas_id'     => $kelasId,
                'type'         => $type,
                'title'        => $row->title,
                'target'       => $row->target,
                'state'        => 'published',
                'published_at' => $row->created_at ?? now(),
                'due_date'     => null,
                'notes'        => null,
                'metadata'     => null,
                'created_by'   => null,
                'updated_by'   => null,
                'created_at'   => $row->created_at ?? now(),
                'updated_at'   => $row->updated_at ?? now(),
            ]);

            DB::table('assignment_members')->insert([
                'tenant_id'     => $row->tenant_id,
                'assignment_id' => $assignmentId,
                'santri_id'     => $row->santri_id,
                'progress'      => $row->achievement,
                'status'        => $row->status ?? 'belum',
                'score'         => null,
                'notes'         => $row->notes,
                'metadata'      => null,
                'updated_by'    => null,
                'created_at'    => $row->created_at ?? now(),
                'updated_at'    => $row->updated_at ?? now(),
            ]);
        }
    }

    // =====================================================================
    // Vocabulary
    // =====================================================================

    private function migrateVocabulary(): void
    {
        if (! Schema::hasTable('vocabularies')) {
            return;
        }

        $rows = DB::table('vocabularies')->orderBy('id')->get();
        $map = [];

        foreach ($rows as $row) {
            $kelasId = $this->resolveKelasId($row->tenant_id, $row->santri_id, $row->program_id);
            $type = "modern-vocabulary-{$row->type}";

            $assignmentId = $this->firstOrCreateAssignment($map, [
                $row->tenant_id, $row->program_id, $kelasId, $type, $row->word, $row->translation,
            ], [
                'tenant_id'    => $row->tenant_id,
                'program_id'   => $row->program_id,
                'kelas_id'     => $kelasId,
                'type'         => $type,
                'title'        => $row->word,
                'target'       => $row->translation,
                'state'        => 'published',
                'published_at' => $row->created_at ?? now(),
                'due_date'     => null,
                'notes'        => null,
                'metadata'     => json_encode([
                    'language'         => $row->language,
                    'example_sentence' => $row->example_sentence,
                    'category'         => $row->category,
                ]),
                'created_by'   => null,
                'updated_by'   => null,
                'created_at'   => $row->created_at ?? now(),
                'updated_at'   => $row->updated_at ?? now(),
            ]);

            DB::table('assignment_members')->insert([
                'tenant_id'     => $row->tenant_id,
                'assignment_id' => $assignmentId,
                'santri_id'     => $row->santri_id,
                'progress'      => null,
                'status'        => $row->status ?? 'belum',
                'score'         => $row->score,
                'notes'         => $row->notes,
                'metadata'      => null,
                'updated_by'    => null,
                'created_at'    => $row->created_at ?? now(),
                'updated_at'    => $row->updated_at ?? now(),
            ]);
        }
    }

    // =====================================================================
    // Muhadatsah
    // =====================================================================

    private function migrateMuhadatsah(): void
    {
        if (! Schema::hasTable('muhadatsahs')) {
            return;
        }

        $rows = DB::table('muhadatsahs')->orderBy('id')->get();
        $map = [];

        foreach ($rows as $row) {
            $kelasId = $this->resolveKelasId($row->tenant_id, $row->santri_id, $row->program_id);
            $type = "modern-muhadatsah-{$row->type}";

            $assignmentId = $this->firstOrCreateAssignment($map, [
                $row->tenant_id, $row->program_id, $kelasId, $type, $row->topic, $row->content,
            ], [
                'tenant_id'    => $row->tenant_id,
                'program_id'   => $row->program_id,
                'kelas_id'     => $kelasId,
                'type'         => $type,
                'title'        => $row->topic,
                'target'       => $row->content,
                'state'        => 'published',
                'published_at' => $row->created_at ?? now(),
                'due_date'     => null,
                'notes'        => null,
                'metadata'     => json_encode([
                    'category' => $row->category,
                ]),
                'created_by'   => null,
                'updated_by'   => null,
                'created_at'   => $row->created_at ?? now(),
                'updated_at'   => $row->updated_at ?? now(),
            ]);

            DB::table('assignment_members')->insert([
                'tenant_id'     => $row->tenant_id,
                'assignment_id' => $assignmentId,
                'santri_id'     => $row->santri_id,
                'progress'      => null,
                'status'        => 'belum',
                'score'         => $row->score,
                'notes'         => $row->notes,
                'metadata'      => null,
                'updated_by'    => null,
                'created_at'    => $row->created_at ?? now(),
                'updated_at'    => $row->updated_at ?? now(),
            ]);
        }
    }

    // =====================================================================
    // Muhadhoroh
    // =====================================================================

    private function migrateMuhadhoroh(): void
    {
        if (! Schema::hasTable('muhadhorohs')) {
            return;
        }

        $rows = DB::table('muhadhorohs')->orderBy('id')->get();
        $map = [];

        foreach ($rows as $row) {
            $kelasId = $this->resolveKelasId($row->tenant_id, $row->santri_id, $row->program_id);
            $type = "modern-muhadhoroh-{$row->type}";

            $assignmentId = $this->firstOrCreateAssignment($map, [
                $row->tenant_id, $row->program_id, $kelasId, $type, $row->title, $row->theme_id,
            ], [
                'tenant_id'    => $row->tenant_id,
                'program_id'   => $row->program_id,
                'kelas_id'     => $kelasId,
                'type'         => $type,
                'title'        => $row->title,
                'target'       => $row->description,
                'state'        => 'published',
                'published_at' => $row->created_at ?? now(),
                'due_date'     => null,
                'notes'        => null,
                'metadata'     => json_encode([
                    'theme_id' => $row->theme_id,
                    'language' => $row->language,
                ]),
                'created_by'   => null,
                'updated_by'   => null,
                'created_at'   => $row->created_at ?? now(),
                'updated_at'   => $row->updated_at ?? now(),
            ]);

            DB::table('assignment_members')->insert([
                'tenant_id'     => $row->tenant_id,
                'assignment_id' => $assignmentId,
                'santri_id'     => $row->santri_id,
                'progress'      => null,
                'status'        => 'belum',
                'score'         => $row->score,
                'notes'         => $row->notes,
                // performed_at/submission_url/is_video_submission are muhadhoroh-only
                // (not reused by every assignment type), so they live in metadata
                // rather than as physical columns on assignment_members.
                'metadata'      => json_encode([
                    'performed_at'        => $row->performed_at,
                    'submission_url'      => $row->submission_url,
                    'is_video_submission' => (bool) ($row->is_video_submission ?? false),
                ]),
                'updated_by'    => null,
                'created_at'    => $row->created_at ?? now(),
                'updated_at'    => $row->updated_at ?? now(),
            ]);
        }
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resolveKelasId(int $tenantId, int $santriId, int $programId): ?int
    {
        $row = DB::table('santri_programs')
            ->where('tenant_id', $tenantId)
            ->where('santri_id', $santriId)
            ->where('program_id', $programId)
            ->first();

        return $row?->kelas_id;
    }

    /**
     * Look up (or create) the shared Assignment for a business key.
     *
     * $businessKey identifies "what this assignment is" — tenant, program,
     * class, type, and the core identity fields (title/target/etc). It is
     * hashed only as an efficient array key for the in-memory $map cache
     * built while iterating legacy rows; the hash itself carries no meaning.
     */
    private function firstOrCreateAssignment(array &$map, array $businessKey, array $assignmentData): int
    {
        $key = $this->businessKeyHash($businessKey);

        if (! isset($map[$key])) {
            $map[$key] = DB::table('assignments')->insertGetId($assignmentData);
        }

        return $map[$key];
    }

    private function businessKeyHash(array $parts): string
    {
        return sha1(implode('|', array_map(fn ($p) => $p ?? 'NULL', $parts)));
    }
};
