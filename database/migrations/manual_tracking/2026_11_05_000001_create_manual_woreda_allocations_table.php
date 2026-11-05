<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A regional bureau's split of its zone allocation between woredas. Kept apart from
 * manual_allocations, whose zone rows are read as the zone's total, so the woreda shares
 * are never counted on top of them. Imports record which level they filled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_woreda_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_allocation_import_id')->nullable()->constrained('manual_allocation_imports')->nullOnDelete();
            $table->string('academic_year', 20);
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->foreignId('woreda_id')->constrained('woredas')->cascadeOnDelete();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->string('edition', 20);
            $table->unsignedBigInteger('quantity');
            $table->string('source_sheet', 100)->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'woreda_id', 'manual_book_id', 'edition'], 'manual_woreda_allocation_unique');
            $table->index(['zone_id', 'academic_year'], 'manual_woreda_allocation_zone_idx');
        });

        Schema::table('manual_allocation_imports', function (Blueprint $table): void {
            $table->string('level', 20)->default('region')->after('academic_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_woreda_allocations');

        Schema::table('manual_allocation_imports', function (Blueprint $table): void {
            $table->dropColumn('level');
        });
    }
};
