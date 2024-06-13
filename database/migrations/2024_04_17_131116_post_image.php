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
        Schema::dropIfExists('POST_IMAGE');
        Schema::create('POST_IMAGE', function (Blueprint $table) {
            $table->unsignedBigInteger('POST_ID'); 
            $table->unsignedBigInteger('IMAGE_ID');
            $table->primary(['POST_ID', 'IMAGE_ID']);

            $table->foreign('IMAGE_ID')->references('IMAGE_ID')->on('IMAGE');
            $table->foreign('POST_ID')->references('POST_ID')->on('POST'); 
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
