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
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('telephone')->nullable();
            $table->string('bank')->nullable();
            $table->double('x-coordinate')->nullable();
            $table->double('y-coordinate')->nullable();
            $table->string('gps')->nullable();
            $table->year('year')->nullable();
            $table->string('old_code')->nullable();
            $table->string('new_code')->nullable();
            $table->json('facilities')->nullable();

            $table->foreignId('assigned_user_id')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            
            $table->boolean('location')->nullable();
            $table->boolean('status')->default(0)->nullable();


            $table->foreignId('organization_type_id')->nullable()->constrained();
            $table->foreignId('country_id')->default(1)->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('zone_id')->nullable()->nullable()->constrained();
            $table->foreignId('woreda_id')->nullable()->nullable()->constrained();
            $table->foreignId('sector_id')->nullable()->constrained();
            $table->foreignId('ownership_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            
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
