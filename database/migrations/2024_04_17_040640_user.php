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
        Schema::dropIfExists('USER');
        Schema::create('USER', function (Blueprint $table) {
            $table->bigIncrements("USER_ID"); 
            $table->string('EMAIL')->unique();
            $table->string('PASSWORD');
            $table->string('PHONE')->unique();
            $table->string('NAME');
            $table->date('BIRTHDAY');
            $table->boolean('GENDER');
            $table->unsignedBigInteger('AVT_IMAGE_ID')->nullable();  

            $table->foreign('AVT_IMAGE_ID')->references('IMAGE_ID')->on('IMAGE'); 
            $table->timestamp('email_verified_at')->nullable();

            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('USER');
    }
};
