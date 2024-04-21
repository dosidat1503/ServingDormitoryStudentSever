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
        Schema::dropIfExists('ORDER');
        Schema::create('ORDER', function (Blueprint $table) {
            $table->bigIncrements("ORDER_ID");
            $table->unsignedBigInteger('SHOP_ID');
            $table->unsignedBigInteger('USER_ID');
            $table->unsignedBigInteger('ORDER_ADDRESS_ID');
            $table->integer('VOUCHER_ID');
            $table->string('PAYMENT_METHOD');
            $table->integer('STATUS');
            $table->bigInteger('TOTAL_PAYMENT');
            $table->timestamp('DATE');

            $table->foreign('USER_ID')->references('USER_ID')->on('USER');
            $table->foreign('SHOP_ID')->references('SHOP_ID')->on('SHOP');
            $table->foreign('ORDER_ADDRESS_ID')->references('ADDRESS_ID')->on('ADDRESS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ORDER');
    }
};
