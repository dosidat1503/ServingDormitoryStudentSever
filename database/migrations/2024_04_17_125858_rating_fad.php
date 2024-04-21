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
        Schema::dropIfExists('RATING_FAD');
        Schema::create('RATING_FAD', function (Blueprint $table) {
            $table->bigIncrements('RATING_ID');
            $table->unsignedBigInteger('ORDER_DETAIL_ID');
            $table->unsignedBigInteger('USER_ID');
            $table->timestamp('DATE');
            $table->string('CONTENT');
            $table->integer('STAR_QUANTITY'); 

            $table->foreign('ORDER_DETAIL_ID')->references('ORDER_DETAIL_ID')->on('ORDER_DETAIL'); 
            $table->foreign('USER_ID')->references('USER_ID')->on('USER'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        Schema::dropIfExists('RATING_FAD');
    }
};
