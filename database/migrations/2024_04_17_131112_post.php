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
        Schema::dropIfExists('POST');
        Schema::create('POST', function (Blueprint $table) {
            $table->bigIncrements('POST_ID');
            $table->unsignedBigInteger('USER_ID');
            $table->longText('CONTENT'); 
            $table->integer('TOPIC');
            $table->bigInteger('LIKE_QUANTITY')->default(0);
            $table->boolean('IS_DELETED')->default(false);
            $table->timestamp('TIME');
 
            $table->foreign('USER_ID')->references('USER_ID')->on('USER'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('POST');
    }
};
