<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('raport_nilai_components')) {
            return;
        }

        Schema::create('raport_nilai_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('raport_nilai_id');
            $table->unsignedBigInteger('assessment_type_id')->nullable();
            $table->string('assessment_code', 50);
            $table->string('assessment_label', 100);
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('contribution', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('raport_nilai_id')->references('id')->on('raport_nilai')->cascadeOnDelete();
            $table->foreign('assessment_type_id')->references('id')->on('assessment_types')->nullOnDelete();

            $table->unique(
                ['raport_nilai_id', 'assessment_code'],
                'raport_nilai_components_unique'
            );
            $table->index(['tenant_id', 'raport_nilai_id'], 'rnc_tenant_raport_nilai_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raport_nilai_components');
    }
};
