<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ustadz_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('ustadz')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->enum('program', ['diniyah', 'pesantren'])->default('diniyah');
            $table->timestamps();

            // Indexes for lookup performance
            $table->index(['tenant_id', 'program']);
            $table->index(['ustadz_id', 'kelas_id']);
            $table->index(['kelas_id', 'subject_id']);
            $table->index(['tenant_id', 'ustadz_id', 'kelas_id', 'program'], 'ustadz_kelas_lookup');
        });

        // Note: MySQL 8.0.13+ supports functional indexes with COALESCE,
        // but MariaDB < 10.5 does not. We skip the functional unique index
        // and rely on application-level validation to prevent duplicates.
        // For strict uniqueness, use a generated column approach or upgrade MySQL.
        
        // Add index for subject_id lookups (without unique constraint)
        if (\DB::getDriverName() === 'mysql') {
            \DB::statement("
                CREATE INDEX ustadz_kelas_subject_lookup 
                ON ustadz_kelas(tenant_id, ustadz_id, kelas_id, program, subject_id)
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ustadz_kelas');
    }
};
