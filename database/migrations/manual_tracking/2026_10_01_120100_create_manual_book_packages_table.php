<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('manual_book_packages')) {
            return;
        }

        Schema::create('manual_book_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_book_id')->constrained('manual_books')->cascadeOnDelete();
            $table->string('package_code')->unique();
            $table->unsignedInteger('no_of_packages')->default(1);
            $table->unsignedInteger('books_per_package')->default(0);
            $table->unsignedBigInteger('total_books')->default(0);
            $table->unsignedBigInteger('current_balance')->default(0);
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_book_packages');
    }
};
