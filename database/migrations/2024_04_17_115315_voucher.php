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
        Schema::dropIfExists('VOUCHER');
        Schema::create('VOUCHER', function (Blueprint $table) {
            $table->bigIncrements("VOUCHER_ID");
            $table->string('VOUCHER_CODE');
            $table->unsignedBigInteger('SHOP_ID');
            $table->integer('DISCOUNT_VALUE');
            $table->integer('MIN_ORDER_TOTAL');
            $table->date('START_DATE');
            $table->date('EXPIRATION_DATE');
            $table->integer('MAX_QUANTITY'); 

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
