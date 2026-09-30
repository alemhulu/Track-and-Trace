<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('access_level', 30)->default('organization')->after('organization_id');
            $table->index('access_level');
        });

        DB::statement("\n            UPDATE users\n            SET access_level = CASE\n                WHEN woreda_id IS NOT NULL THEN 'woreda'\n                WHEN zone_id IS NOT NULL THEN 'zone'\n                WHEN region_id IS NOT NULL THEN 'region'\n                WHEN organization_id IS NOT NULL THEN 'organization'\n                ELSE 'national'\n            END\n        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['access_level']);
            $table->dropColumn('access_level');
        });
    }
};
