<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manual_distribution_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_distribution_id')->constrained('manual_distributions')->cascadeOnDelete();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->foreignId('manual_book_package_id')->nullable()->constrained('manual_book_packages')->nullOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('source_balance_before')->default(0);
            $table->unsignedBigInteger('source_balance_after')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_distribution_lines');
    }
};
