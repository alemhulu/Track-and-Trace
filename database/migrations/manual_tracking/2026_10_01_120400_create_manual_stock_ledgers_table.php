<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manual_stock_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->foreignId('manual_book_package_id')->nullable()->constrained('manual_book_packages')->nullOnDelete();
            $table->string('movement_type');
            $table->bigInteger('movement_qty');
            $table->unsignedBigInteger('balance_after')->default(0);
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->unsignedBigInteger('acted_by')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('woreda_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['manual_book_id', 'created_at'], 'manual_stock_book_created_idx');
            $table->index(['organization_id', 'region_id', 'zone_id', 'woreda_id'], 'manual_stock_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_stock_ledgers');
    }
};
