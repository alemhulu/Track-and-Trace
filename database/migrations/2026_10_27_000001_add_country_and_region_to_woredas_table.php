<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Woreda model, seeders and manual tracking already write these columns.
     */
    public function up(): void
    {
        Schema::table('woredas', function (Blueprint $table): void {
            if (! Schema::hasColumn('woredas', 'country_id')) {
                $table->unsignedBigInteger('country_id')->nullable()->after('code');
            }

            if (! Schema::hasColumn('woredas', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable()->after('country_id');
            }
        });

        // Backfill from the parent zone
        DB::statement('
            UPDATE woredas
            INNER JOIN zones ON zones.id = woredas.zone_id
            SET woredas.country_id = COALESCE(woredas.country_id, zones.country_id),
                woredas.region_id = COALESCE(woredas.region_id, zones.region_id)
        ');
    }

    public function down(): void
    {
        Schema::table('woredas', function (Blueprint $table): void {
            $table->dropColumn(['country_id', 'region_id']);
        });
    }
};
