<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allocation broken down by zone, next to the region total. A row without a zone is the
 * region's allocation; rows with a zone split it between the region's zones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_allocations', function (Blueprint $table): void {
            $table->foreignId('zone_id')->nullable()->after('region_id')->constrained('zones')->cascadeOnDelete();
            $table->dropUnique('manual_allocation_unique');
            $table->unique(['academic_year', 'region_id', 'zone_id', 'manual_book_id', 'edition'], 'manual_allocation_unique');
        });
    }

    public function down(): void
    {
        Schema::table('manual_allocations', function (Blueprint $table): void {
            $table->dropUnique('manual_allocation_unique');
            $table->dropConstrainedForeignId('zone_id');
            $table->unique(['academic_year', 'region_id', 'manual_book_id', 'edition'], 'manual_allocation_unique');
        });
    }
};
