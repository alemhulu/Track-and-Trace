<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns manual tracking into quantity tracking per location:
 * titles linked to region/grade/subject with their printed QR codes, stock held by an
 * organization, and shipments that are dispatched by one side and confirmed by the other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_books', function (Blueprint $table): void {
            $table->foreignId('region_id')->nullable()->after('subject_name')->constrained('regions')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->after('region_id')->constrained('grades')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->after('grade_id')->constrained('subjects')->nullOnDelete();
            $table->boolean('has_teacher_guide')->default(true)->after('subject_id');
            $table->unsignedSmallInteger('students_per_package')->default(40)->after('has_teacher_guide');
        });

        // Printed QR text and code for each edition of a title
        Schema::create('manual_book_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->string('edition', 20);
            $table->string('payload');
            $table->string('normalized_payload')->unique();
            $table->string('print_code')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('manual_stock_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->string('edition', 20);
            $table->unsignedBigInteger('on_hand')->default(0);
            $table->unsignedBigInteger('in_transit_out')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'manual_book_id', 'edition'], 'manual_stock_balance_unique');
        });

        Schema::table('manual_distributions', function (Blueprint $table): void {
            // Rows created before this workflow were final on save
            $table->string('status', 30)->default('received')->after('reference')->index();
            $table->string('carrier')->nullable()->after('remarks');
            $table->string('vehicle_no')->nullable()->after('carrier');
            $table->string('driver_name')->nullable()->after('vehicle_no');
            $table->string('driver_phone')->nullable()->after('driver_name');
            $table->string('waybill_no')->nullable()->after('driver_phone');
            $table->timestamp('expected_at')->nullable()->after('waybill_no');
            $table->timestamp('received_at')->nullable()->after('expected_at');
            $table->unsignedBigInteger('received_by')->nullable()->after('received_at');
            $table->text('receipt_notes')->nullable()->after('received_by');
            $table->uuid('client_uuid')->nullable()->unique()->after('receipt_notes');
        });

        Schema::table('manual_distribution_lines', function (Blueprint $table): void {
            $table->unsignedInteger('full_packages')->default(0)->after('manual_book_package_id');
            $table->unsignedSmallInteger('partial_package_books')->default(0)->after('full_packages');
            $table->unsignedSmallInteger('partial_teacher_guides')->default(0)->after('partial_package_books');
            $table->unsignedBigInteger('teacher_guides')->default(0)->after('quantity');
            $table->unsignedBigInteger('received_quantity')->nullable()->after('teacher_guides');
            $table->unsignedBigInteger('received_teacher_guides')->nullable()->after('received_quantity');
            $table->unsignedBigInteger('damaged_quantity')->default(0)->after('received_teacher_guides');
            $table->unsignedBigInteger('damaged_teacher_guides')->default(0)->after('damaged_quantity');
        });

        DB::table('manual_distribution_lines')->update([
            'received_quantity' => DB::raw('quantity'),
            'received_teacher_guides' => 0,
        ]);

        Schema::table('manual_stock_ledgers', function (Blueprint $table): void {
            $table->string('edition', 20)->default('student')->after('manual_book_package_id');
            $table->foreignId('manual_distribution_line_id')->nullable()->after('ref_id')
                ->constrained('manual_distribution_lines')->nullOnDelete();
            $table->uuid('client_uuid')->nullable()->unique()->after('notes');
            $table->timestamp('occurred_at')->nullable()->after('client_uuid');
            $table->index(['organization_id', 'manual_book_id', 'edition'], 'manual_stock_holder_idx');
        });

        Schema::create('manual_discrepancies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_distribution_line_id')->constrained('manual_distribution_lines')->cascadeOnDelete();
            $table->string('edition', 20);
            $table->string('type', 20);
            $table->unsignedBigInteger('quantity');
            $table->string('status', 20)->default('open')->index();
            $table->string('resolution', 30)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->unsignedBigInteger('raised_by')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        if (! DB::table('organization_types')->where('name', 'Logistics Hub')->exists()) {
            DB::table('organization_types')->insert(['name' => 'Logistics Hub', 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_discrepancies');

        Schema::table('manual_stock_ledgers', function (Blueprint $table): void {
            $table->dropIndex('manual_stock_holder_idx');
            $table->dropConstrainedForeignId('manual_distribution_line_id');
            $table->dropColumn(['edition', 'client_uuid', 'occurred_at']);
        });

        Schema::table('manual_distribution_lines', function (Blueprint $table): void {
            $table->dropColumn([
                'full_packages', 'partial_package_books', 'partial_teacher_guides', 'teacher_guides',
                'received_quantity', 'received_teacher_guides', 'damaged_quantity', 'damaged_teacher_guides',
            ]);
        });

        Schema::table('manual_distributions', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropUnique(['client_uuid']);
            $table->dropColumn([
                'status', 'carrier', 'vehicle_no', 'driver_name', 'driver_phone', 'waybill_no',
                'expected_at', 'received_at', 'received_by', 'receipt_notes', 'client_uuid',
            ]);
        });

        Schema::dropIfExists('manual_stock_balances');
        Schema::dropIfExists('manual_book_codes');

        Schema::table('manual_books', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('region_id');
            $table->dropConstrainedForeignId('grade_id');
            $table->dropConstrainedForeignId('subject_id');
            $table->dropColumn(['has_teacher_guide', 'students_per_package']);
        });

        DB::table('organization_types')->where('name', 'Logistics Hub')->delete();
    }
};
