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
        Schema::create('ware_houses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('branch');
            $table->foreignId('organization_id');
            $table->foreignId('assigned_user_id')->nullable();
            $table->foreignId('country_id')->default(1);
            $table->foreignId('region_id')->nullable();
            $table->foreignId('zone_id')->nullable();
            $table->foreignId('woreda_id')->nullable();
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
        Schema::dropIfExists('ware_houses');
    }
};
