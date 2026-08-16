<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_ustadz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ustadz_id')->constrained('ustadz')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa']);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['ustadz_id', 'date']);
            $table->index(['ustadz_id']);
            $table->index(['date']);
            $table->index(['status']);
            $table->index(['ustadz_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_ustadz');
    }
};
