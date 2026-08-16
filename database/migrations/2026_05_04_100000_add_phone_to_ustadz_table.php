<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ustadz', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('bio')->comment('Nomor HP/WhatsApp ustadz');
            $table->index(['phone']);
        });
    }

    public function down(): void
    {
        Schema::table('ustadz', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropColumn('phone');
        });
    }
};
