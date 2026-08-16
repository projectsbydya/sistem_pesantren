<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_pengajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('platform', ['zoom', 'gmeet', 'youtube'])->default('youtube');
            $table->string('link_url');
            $table->string('meeting_id')->nullable();
            $table->string('passcode')->nullable();
            $table->datetime('jadwal_mulai');
            $table->datetime('jadwal_selesai')->nullable();
            $table->enum('status', ['scheduled', 'live', 'ended'])->default('scheduled');
            $table->string('thumbnail_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['jadwal_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_pengajian');
    }
};
