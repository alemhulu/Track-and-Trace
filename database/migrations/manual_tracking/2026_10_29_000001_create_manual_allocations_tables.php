<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Ministry's regional allocation: how many student texts and teacher guides of each
 * title a region is to receive in an academic year. It is the baseline shipments and
 * receipts are measured against. Titles carry the language and a stable source key so a
 * re-import updates the same title instead of adding another.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_books', function (Blueprint $table): void {
            $table->string('language', 100)->nullable()->after('subject_name');
            $table->string('print_code', 50)->nullable()->after('code');
            $table->string('source_key')->nullable()->unique()->after('print_code');
        });

        Schema::create('manual_allocation_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('file_name');
            $table->string('academic_year', 20);
            $table->unsignedBigInteger('imported_by')->nullable();
            $table->unsignedInteger('line_count')->default(0);
            $table->unsignedInteger('title_count')->default(0);
            $table->unsignedBigInteger('total_quantity')->default(0);
            $table->json('region_totals')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_allocation_import_id')->nullable()->constrained('manual_allocation_imports')->nullOnDelete();
            $table->string('academic_year', 20);
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->string('edition', 20);
            $table->unsignedBigInteger('quantity');
            $table->boolean('type_confirmed')->default(true);
            $table->string('source_title')->nullable();
            $table->string('source_sheet', 100)->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'region_id', 'manual_book_id', 'edition'], 'manual_allocation_unique');
            $table->index(['region_id', 'academic_year'], 'manual_allocation_region_idx');
        });

        // The subject seeder used to misspell this; the allocation import uses the correct name
        if (! DB::table('subjects')->where('name', 'Environmental Science')->exists()) {
            DB::table('subjects')->where('name', 'Environmental Sience')->update(['name' => 'Environmental Science']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_allocations');
        Schema::dropIfExists('manual_allocation_imports');

        Schema::table('manual_books', function (Blueprint $table): void {
            $table->dropUnique(['source_key']);
            $table->dropColumn(['language', 'print_code', 'source_key']);
        });
    }
};
