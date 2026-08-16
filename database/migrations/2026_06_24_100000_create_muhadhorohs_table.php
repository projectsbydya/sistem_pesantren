<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muhadhorohs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('santri_id');
            $table->string('type', 30);
            $table->string('title', 255);
            $table->unsignedBigInteger('theme_id')->nullable();
            $table->string('language', 10)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->text('notes')->nullable();
            $table->date('performed_at')->nullable();
            $table->boolean('is_video_submission')->default(false);
            $table->string('submission_url')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('theme_id')->references('id')->on('muhadhoroh_themes')->nullOnDelete();

            $table->index(['tenant_id', 'program_id', 'santri_id']);
            $table->index(['tenant_id', 'program_id', 'type']);
            $table->index(['tenant_id', 'santri_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muhadhorohs');
    }
};
