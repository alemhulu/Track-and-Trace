<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('paper_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paper_sizes');
    }
};
