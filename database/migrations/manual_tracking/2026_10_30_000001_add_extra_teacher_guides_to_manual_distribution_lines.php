<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Teacher guides beyond one per package, packed and counted on their own
    public function up(): void
    {
        Schema::table('manual_distribution_lines', function (Blueprint $table): void {
            $table->unsignedInteger('extra_teacher_guides')->default(0)->after('partial_teacher_guides');
        });
    }

    public function down(): void
    {
        Schema::table('manual_distribution_lines', function (Blueprint $table): void {
            $table->dropColumn('extra_teacher_guides');
        });
    }
};
