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
            $table->foreignId('book_id')->nullable()->constrained();
            $table->foreignId('printer_organization_id')->default(1);
            $table->unsignedBigInteger('no_of_books');
            $table->unsignedBigInteger('no_of_packages');
            $table->unsignedBigInteger('start_barcode');
            $table->unsignedBigInteger('end_barcode');
            $table->unsignedBigInteger('start_qrcode');
            $table->unsignedBigInteger('end_qrcode');
            $table->boolean('status');
            
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
