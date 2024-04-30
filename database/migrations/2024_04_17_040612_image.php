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
        Schema::dropIfExists('IMAGE');
        Schema::create('IMAGE', function (Blueprint $table) {
            $table->string('IMAGE_ID', 255)->primary();  
            $table->string('URL'); 
            $table->BigInteger('USER_ID')->nullable();
 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('IMAGE');
    }
};
