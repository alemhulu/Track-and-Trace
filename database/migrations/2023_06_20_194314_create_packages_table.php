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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ware_house_id')->constrained();
            $table->foreignId('print_order_id')->constrained();
            $table->foreignId('sender_organization_id')->nullable();
            $table->foreignId('receiver_organization_id')->nullable();
            $table->unsignedInteger('step')->nullable();
            $table->json('Book_codes')->nullable();
            $table->unsignedInteger('received')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('balance')->default(0);
            $table->unsignedBigInteger('no_of_books')->nullable();
            $table->unsignedBigInteger('books_per_package')->nullable();
            $table->unsignedBigInteger('qrcode_start')->nullable();
            $table->unsignedBigInteger('qrcode_end')->nullable();
            $table->unsignedBigInteger('barcode_start')->nullable();
            $table->unsignedBigInteger('barcode_end')->nullable();
            $table->string('expected_send_time')->nullable();
            $table->string('actual_send_time')->nullable();
            $table->string('expected_delivery_school_time')->nullable();
            $table->string('actual_delivery_school_time')->nullable();
            $table->boolean('request_status')->nullable();
            $table->boolean('delivery_status')->nullable();
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
        Schema::dropIfExists('packages');
    }
};
