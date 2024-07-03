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
        Schema::dropIfExists('ORDER_DETAIL');
        Schema::create('ORDER_DETAIL', function (Blueprint $table) {
            $table->bigIncrements("ORDER_DETAIL_ID");
            $table->unsignedBigInteger('ORDER_ID');
            $table->unsignedBigInteger('FAD_ID');
            $table->integer('QUANTITY');
            $table->integer('PRICE'); 
            $table->string('SIZE')->nullable(); 
            $table->timestamp('DATE_RATE')->nullable();
            $table->string('CONTENT_RATE')->nullable();
            $table->integer('STAR_QUANTITY_RATE')->nullable(); 
            $table->integer('ID_PARENT_OD_OF_THIS_OD')->nullable(); 

            $table->foreign('FAD_ID')->references('FAD_ID')->on('FAD'); 
            $table->foreign('ORDER_ID')->references('ORDER_ID')->on('ORDER'); 
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { 
        Schema::dropIfExists('ORDER_DETAIL');
    }
};
