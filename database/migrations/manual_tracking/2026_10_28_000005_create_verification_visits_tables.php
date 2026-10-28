<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spot checks by a supervisor from outside the school: books counted on the shelf against
 * the system, and a random sample of students asked to show the books recorded as theirs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->unsignedSmallInteger('academic_year');
            $table->foreignId('visited_by')->constrained('users');
            $table->timestamp('visited_at');
            $table->unsignedInteger('stock_lines')->default(0);
            $table->unsignedInteger('stock_lines_matching')->default(0);
            $table->unsignedInteger('students_checked')->default(0);
            $table->unsignedInteger('books_expected')->default(0);
            $table->unsignedInteger('books_seen')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'academic_year'], 'verification_school_idx');
        });

        Schema::create('verification_visit_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('verification_visit_id')->constrained('verification_visits')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->foreignId('manual_book_id')->constrained('manual_books');
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->integer('expected');
            $table->integer('found');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_visit_items');
        Schema::dropIfExists('verification_visits');
    }
};
