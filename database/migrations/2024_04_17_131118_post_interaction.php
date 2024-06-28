<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('POST_INTERACTION');
        Schema::create('POST_INTERACTION', function (Blueprint $table) {
            $table->bigIncrements('POST_INTERACTION_ID');
            $table->unsignedBigInteger('POST_ID');
            $table->unsignedBigInteger('USER_ID');
            $table->integer('IS_LIKE')->nullable(); 
            $table->integer('IS_SAVE')->nullable();  

            $table->foreign('POST_ID')->references('POST_ID')->on('POST'); 
            $table->foreign('USER_ID')->references('USER_ID')->on('USER'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
