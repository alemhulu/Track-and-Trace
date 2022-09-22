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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('website')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('logo')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobile');
            $table->foreignId('assigned_user_id');
            $table->foreignId('country_id')->constrained();
            $table->foreignId('region_id')->constrained();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->foreignId('woreda_id')->nullable()->constrained();
            $table->foreignId('kebele_id')->nullable()->constrained();
            $table->foreignId('organization_type_id')->constrained();
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
        Schema::dropIfExists('organizations');
    }
};
