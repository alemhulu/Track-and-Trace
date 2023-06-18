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
        Schema::create('organization_types', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->nullable();
            $table->string('old_code')->nullable();
            $table->string('new_code')->unique();
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->string('tel')->nullable();
            $table->boolean('location')->nullable();
            $table->double('x-coordinate')->nullable();
            $table->double('y-coordinate')->nullable();
            $table->boolean('status')->default(1)->nullable();
            $table->string('bank')->nullable();


            $table->foreignId('organisation_type_id')->nullable()->constrained();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('region_id')->nullable()->constrained();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->foreignId('woreda_id')->nullable()->constrained();
            
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('ownership_id')->nullable()->constrained();
            $table->foreignId('sector_id')->nullable()->constrained();
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
        Schema::dropIfExists('organization_types');
    }
};
