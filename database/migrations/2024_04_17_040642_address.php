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
        Schema::dropIfExists('ADDRESS');
        Schema::create('ADDRESS', function (Blueprint $table) {
            $table->bigIncrements("ADDRESS_ID");
            $table->string('NAME')->nullable();
            $table->string('PHONE')->nullable();
            $table->string('DETAIL');
            $table->string('COMMUNE')->nullable();
            $table->string('DISTRICT')->nullable();
            $table->string('PROVINCE')->nullable();
            $table->boolean('IS_DELETED');
            $table->boolean('IS_DEFAULT')->nullable();
            $table->unsignedBigInteger('USER_ID');

            $table->foreign('USER_ID')->references('USER_ID')->on('USER');
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        Schema::dropIfExists('ADDRESS');
    }
};
