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
            $table->string('email')->unique();
            $table->string('logo')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobile');
            $table->string('contact_person_name');
            $table->string('contact_person_mobile');
            $table->foreignId('country_id')->defualt('Ethiopia')->constrained();
            $table->foreignId('city_id')->nullable()->constrained();
            $table->foreignId('sub_city_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->foreignId('woreda_id')->nullable()->constrained();
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
