<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Screenshots / attachments uploaded to bug reports.
     *
     * Files are stored on disk; only metadata lives here.
     */
    public function up(): void
    {
        Schema::create('bug_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bug_report_id')->constrained('bug_reports')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'bug_report_id']);
            $table->index(['bug_report_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_report_attachments');
    }
};
