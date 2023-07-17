<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('print_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('qrcode_start')->nullable();
            $table->unsignedBigInteger('qrcode_end')->nullable();
            $table->unsignedBigInteger('barcode_start')->nullable();
            $table->unsignedBigInteger('barcode_end')->nullable();
            $table->unsignedBigInteger('book_per_package')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_orders', function (Blueprint $table) {
            //
        });
    }
};
