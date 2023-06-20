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
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
            $table->foreignId('printer_id')->nullable()->constrained();
            $table->foreignId('moe_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->foreignId('woreda_id')->nullable()->constrained();
            $table->foreignId('school_id')->nullable()->constrained();
            $table->unsignedInteger('step')->default(1);
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
        Schema::dropIfExists('distributions');
    }
};
