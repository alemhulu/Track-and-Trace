<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('print_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_organization_id');
            $table->foreignId('printer_organization_id');
            $table->foreignId('book_id')->nullable()->constrained();
            $table->unsignedBigInteger('no_of_books')->nullable();
            $table->unsignedBigInteger('no_of_packages')->nullable();
            $table->json('Book_codes')->nullable();
            $table->string('expected_print_time')->nullable();
            $table->string('actual_print_time')->nullable();
            $table->unsignedInteger('print_status');
            $table->unsignedInteger('request_status');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_orders');
    }
};
