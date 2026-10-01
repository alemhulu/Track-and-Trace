<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('manual_books')) {
            return;
        }

        Schema::create('manual_books', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('title');
            $table->string('grade_name')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('isbn')->nullable();
            $table->string('edition')->nullable();
            $table->unsignedBigInteger('total_copies')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_books');
    }
};
