<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('name');
            $table->index(['tenant_id', 'nik']);
        });
    }

    public function down(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'nik']);
            $table->dropColumn('nik');
        });
    }
};
