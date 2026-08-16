<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ustadz', 'specialization')) {
            return;
        }

        Schema::table('ustadz', function (Blueprint $table) {
            $indexName = 'ustadz_specialization_index';

            if (Schema::hasIndex('ustadz', $indexName)) {
                $table->dropIndex($indexName);
            }

            $table->dropColumn('specialization');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('ustadz', 'specialization')) {
            return;
        }

        Schema::table('ustadz', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('bio');
            $table->index(['specialization']);
        });
    }
};
