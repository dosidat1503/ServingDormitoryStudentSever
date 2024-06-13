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
        Schema::dropIfExists('FAD');
        Schema::create('FAD', function (Blueprint $table) {
            $table->bigIncrements("FAD_ID");
            $table->string('FAD_NAME');
            $table->integer('FAD_PRICE');
            $table->unsignedBigInteger('IMAGE_ID');
            $table->unsignedBigInteger('SHOP_ID');
            $table->integer('ID_PARENTFADOFTOPPING')->nullable();
            $table->longText('DESCRIPTION');  
            $table->boolean('IS_DELETED');

            $table->foreign('IMAGE_ID')->references('IMAGE_ID')->on('IMAGE');
            $table->foreign('SHOP_ID')->references('SHOP_ID')->on('SHOP'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('FAD');
    } 
};
