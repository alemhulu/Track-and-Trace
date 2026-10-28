<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School-level records: enrolment per grade, the student register, and which student
 * or teacher holds which book. Academic years are Ethiopian calendar years (e.g. 2019).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_enrolments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->unsignedSmallInteger('academic_year');
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->unsignedInteger('male')->default(0);
            $table->unsignedInteger('female')->default(0);
            $table->unsignedSmallInteger('sections')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'academic_year', 'grade_id'], 'school_enrolment_unique');
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->unsignedSmallInteger('academic_year');
            $table->string('student_no', 50);
            $table->string('name');
            $table->char('sex', 1);
            $table->foreignId('grade_id')->constrained('grades');
            $table->string('section', 10)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['organization_id', 'academic_year', 'student_no'], 'student_register_unique');
            $table->index(['organization_id', 'academic_year', 'grade_id', 'section'], 'student_class_idx');
        });

        Schema::create('student_book_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('manual_book_id')->constrained('manual_books');
            $table->timestamp('issued_at');
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->string('condition', 20)->default('new');
            $table->timestamp('returned_at')->nullable();
            $table->unsignedBigInteger('returned_by')->nullable();
            $table->string('return_condition', 20)->nullable();
            $table->uuid('client_uuid')->nullable()->unique();
            $table->timestamps();

            $table->index(['student_id', 'manual_book_id', 'returned_at'], 'student_issue_holding_idx');
        });

        Schema::create('teacher_guide_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->unsignedSmallInteger('academic_year');
            $table->foreignId('manual_book_id')->constrained('manual_books');
            $table->string('teacher_name');
            $table->string('section', 10)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamp('issued_at');
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->unsignedBigInteger('returned_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_guide_issues');
        Schema::dropIfExists('student_book_issues');
        Schema::dropIfExists('students');
        Schema::dropIfExists('school_enrolments');
    }
};
