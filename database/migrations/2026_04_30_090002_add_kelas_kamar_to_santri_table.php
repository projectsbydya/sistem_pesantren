<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->foreignId('kelas_id')
                ->nullable()
                ->after('school_level')
                ->constrained('kelas')
                ->nullOnDelete();

            $table->foreignId('kamar_id')
                ->nullable()
                ->after('kelas_id')
                ->constrained('kamar')
                ->nullOnDelete();

            $table->boolean('is_mondok')->default(false)->after('kamar_id');

            $table->index(['kelas_id']);
            $table->index(['kamar_id']);
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['kamar_id']);
            $table->dropColumn(['kelas_id', 'kamar_id', 'is_mondok']);
        });
    }
};
