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
            $table->string('EMAIL');
            $table->string('PASSWORD');
            $table->string('PHONE');
            $table->string('NAME');
            $table->date('BIRTHDAY');
            $table->boolean('GENDER');
            $table->string('AVT_IMAGE_ID', 255)->nullable(); 
            $table->unsignedBigInteger('ADDRESS_ID')->nullable();  

            $table->foreign('AVT_IMAGE_ID')->references('IMAGE_ID')->on('IMAGE');
            $table->foreign('ADDRESS_ID')->references('ADDRESS_ID')->on('ADDRESS'); 
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
