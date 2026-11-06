<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The printer's data: cartons packed for a zone (a row per zone, title and carton contents,
 * all carrying the zone's package QR), and book QR texts that several language editions
 * share, so the QR text no longer identifies one title on its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_book_packages', function (Blueprint $table): void {
            $table->string('academic_year', 20)->nullable()->after('manual_book_id');
            $table->foreignId('region_id')->nullable()->after('academic_year')->constrained('regions')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->after('region_id')->constrained('zones')->nullOnDelete();
            $table->string('package_qr_code', 50)->nullable()->after('package_code')->index();
            $table->string('package_qr_text')->nullable()->after('package_qr_code');
            $table->string('carton_type', 50)->nullable()->after('package_qr_text');
            $table->unsignedInteger('teacher_guides_per_package')->default(0)->after('books_per_package');
            $table->unsignedBigInteger('total_teacher_guides')->default(0)->after('total_books');
            $table->string('source_key')->nullable()->unique()->after('notes');

            $table->index(['zone_id', 'manual_book_id'], 'manual_package_zone_book_idx');
        });

        Schema::table('manual_book_codes', function (Blueprint $table): void {
            $table->dropUnique(['normalized_payload']);
            $table->index('normalized_payload');
        });
    }

    public function down(): void
    {
        Schema::table('manual_book_codes', function (Blueprint $table): void {
            $table->dropIndex(['normalized_payload']);
            $table->unique('normalized_payload');
        });

        Schema::table('manual_book_packages', function (Blueprint $table): void {
            $table->dropIndex('manual_package_zone_book_idx');
            $table->dropConstrainedForeignId('zone_id');
            $table->dropConstrainedForeignId('region_id');
            $table->dropUnique(['source_key']);
            $table->dropIndex(['package_qr_code']);
            $table->dropColumn(['academic_year', 'package_qr_code', 'package_qr_text', 'carton_type', 'teacher_guides_per_package', 'total_teacher_guides', 'source_key']);
        });
    }
};
