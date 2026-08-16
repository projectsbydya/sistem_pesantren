<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Migrate the legacy notifications table to Laravel's native database
 * notification schema so the built-in DatabaseChannel works.
 *
 * Legacy schema (id, user_id, type, message, is_read, read_at, timestamps)
 * is transformed into the standard Laravel schema
 * (id, type, notifiable_type, notifiable_id, data, read_at, timestamps).
 *
 * The standard Laravel schema uses a UUID primary key because the built-in
 * DatabaseChannel generates UUID notification identifiers. Legacy integer
 * primary keys are preserved inside the JSON data payload.
 *
 * Existing rows are preserved: the old message/type/read state is moved
 * into the JSON data payload, and notifiable_id points to the original
 * user_id. No tenant_id column is added because tenant isolation is
 * handled via the notifiable User relationship and the tenant_id already
 * stored inside each notification's data payload by the existing
 * notification classes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            $this->createStandardTable();

            return;
        }

        // Already Laravel-compatible: only ensure indexes/columns are present.
        if (Schema::hasColumn('notifications', 'notifiable_type')) {
            $this->ensureStandardIndexes();

            return;
        }

        // Legacy schema detected. Backup, recreate, and migrate.
        $backupTable = 'notifications_legacy_backup_' . date('YmdHis');
        Schema::rename('notifications', $backupTable);

        $this->createStandardTable();
        $this->migrateLegacyToStandard($backupTable);

        Schema::drop($backupTable);
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            $this->createLegacyTable();

            return;
        }

        // Already legacy schema: nothing to reverse.
        if (! Schema::hasColumn('notifications', 'notifiable_type')) {
            return;
        }

        // Standard schema detected. Backup, recreate legacy, and migrate back.
        // Legacy integer IDs are regenerated because the standard schema uses UUIDs.
        $backupTable = 'notifications_standard_backup_' . date('YmdHis');
        Schema::rename('notifications', $backupTable);

        $this->createLegacyTable();
        $this->migrateStandardToLegacy($backupTable);

        Schema::drop($backupTable);
    }

    private function createStandardTable(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    private function createLegacyTable(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['type']);
            $table->index(['is_read']);
            $table->index(['read_at']);
            $table->index(['created_at']);
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    private function ensureStandardIndexes(): void
    {
        $hasMorphIndex = false;
        $indexes = Schema::getIndexes('notifications');

        foreach ($indexes as $index) {
            $columns = $index['columns'] ?? [];
            if (in_array('notifiable_type', $columns, true) && in_array('notifiable_id', $columns, true)) {
                $hasMorphIndex = true;
                break;
            }
        }

        if (! $hasMorphIndex) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }
    }

    private function migrateLegacyToStandard(string $legacyTable): void
    {
        $userClass = \App\Models\User::class;

        DB::table($legacyTable)->orderBy('id')->chunk(100, function ($rows) use ($userClass) {
            $inserts = [];

            foreach ($rows as $row) {
                $readAt = $row->read_at;
                if (empty($readAt) && $row->is_read) {
                    $readAt = $row->updated_at ?? $row->created_at ?? now();
                }

                $inserts[] = [
                    'id' => Str::uuid()->toString(),
                    'type' => $row->type,
                    'notifiable_type' => $userClass,
                    'notifiable_id' => $row->user_id,
                    'data' => json_encode([
                        'message' => $row->message,
                        'legacy_type' => $row->type,
                        'legacy_id' => $row->id,
                        'legacy_is_read' => (bool) $row->is_read,
                    ]),
                    'read_at' => $readAt,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }

            if (! empty($inserts)) {
                DB::table('notifications')->insert($inserts);
            }
        });
    }

    private function migrateStandardToLegacy(string $standardTable): void
    {
        DB::table($standardTable)->orderBy('created_at')->chunk(100, function ($rows) {
            $inserts = [];

            foreach ($rows as $row) {
                $data = json_decode($row->data, true) ?? [];

                $inserts[] = [
                    'user_id' => $row->notifiable_id,
                    'type' => $row->type,
                    'message' => $data['message'] ?? '',
                    'is_read' => $row->read_at !== null ? 1 : 0,
                    'read_at' => $row->read_at,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }

            if (! empty($inserts)) {
                DB::table('notifications')->insert($inserts);
            }
        });
    }
};
