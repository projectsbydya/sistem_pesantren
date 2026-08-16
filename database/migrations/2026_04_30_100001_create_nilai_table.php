<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['diniyah', 'pesantren']);
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('ustadz_id')->nullable()->constrained('ustadz')->nullOnDelete();
            $table->date('tanggal');
            $table->string('materi')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['santri_id', 'tanggal']);
            $table->index(['kelas_id', 'subject_id']);
        });

        // Note: MySQL 8.0.13+ supports functional indexes with COALESCE,
        // but MariaDB < 10.5 does not. We use a composite unique without subject_id
        // and add a separate index for subject lookups. Uniqueness is enforced via
        // application validation for records with NULL vs non-NULL subject_id.
        if (\DB::getDriverName() === 'mysql') {
            \DB::statement("
                ALTER TABLE nilai
                ADD UNIQUE INDEX nilai_unique (
                    tenant_id, santri_id, tanggal, type
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};
