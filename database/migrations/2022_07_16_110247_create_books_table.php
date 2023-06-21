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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->nullable()->constrained();
            $table->foreignId('subject_id')->nullable()->constrained();
            $table->string('isbn')->nullable()->unique();
            $table->unsignedInteger('volume')->nullable();
            $table->unsignedInteger('edition')->nullable();
            $table->boolean('book_type')->nullable();
            $table->string('print_type')->nullable();
            $table->string('paper_size')->nullable();
            $table->string('file_location')->nullable();
            $table->string('front_cover_location')->nullable();
            $table->string('back_cover_location')->nullable();
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
        Schema::dropIfExists('books');
    }
};
