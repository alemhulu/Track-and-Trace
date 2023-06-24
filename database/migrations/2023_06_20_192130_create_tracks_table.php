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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained();
            $table->json('sender_organization_id');
            $table->unsignedBigInteger('no_of_books')->nullable();
            $table->unsignedBigInteger('no_of_packages')->nullable();
            $table->unsignedBigInteger('qrcode_start')->nullable();
            $table->unsignedBigInteger('qrcode_end')->nullable();
            $table->unsignedBigInteger('barcode_start')->nullable();
            $table->unsignedBigInteger('barcode_end')->nullable();
            $table->json('Book_codes')->nullable();
            $table->string('expected_send_time')->nullable();
            $table->string('actual_send_time')->nullable();
            $table->string('expected_delivery_school_time')->nullable();
            $table->string('actual_delivery_school_time')->nullable();
            $table->unsignedInteger('delivery_status')->nullable();
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
        Schema::dropIfExists('tracks');
    }
};
