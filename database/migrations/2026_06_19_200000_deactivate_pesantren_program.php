<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Program "Pesantren" has been absorbed into Pesantren Core (Universal).
 * It is no longer an onboarding program — deactivate it so it doesn't appear
 * in the program selection during onboarding.
 *
 * Existing tenant_programs records are NOT deleted — they remain for
 * backward compatibility (academic data referencing this program_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('programs')
            ->where('slug', 'pesantren')
            ->update([
                'is_active' => false,
                'description' => 'Program pendidikan pesantren klasik yang mencakup kehidupan pondok dan kajian kitab. (Digabung ke Pesantren Core — bukan program onboarding)',
            ]);
    }

    public function down(): void
    {
        DB::table('programs')
            ->where('slug', 'pesantren')
            ->update([
                'is_active' => true,
                'description' => 'Program pendidikan pesantren klasik yang mencakup kehidupan pondok dan kajian kitab.',
            ]);
    }
};
