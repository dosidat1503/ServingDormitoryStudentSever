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
            $table->unsignedBigInteger('USER_ID');
            $table->unsignedBigInteger('ADDRESS_ID'); 
            $table->string('PAYMENT_METHOD');
            $table->integer('STATUS');
            $table->integer('PAYMENT_STATUS');
            $table->bigInteger('TOTAL_PAYMENT');
            $table->string('VOUCHER_CODE', 191)->nullable();
            $table->integer('DISCOUNT_VALUE')->nullable();
            $table->string('NOTE')->nullable();
            $table->timestamp('DATE');

            $table->foreign('USER_ID')->references('USER_ID')->on('USER'); 
            $table->foreign('ADDRESS_ID')->references('ADDRESS_ID')->on('ADDRESS');
            $table->foreign('VOUCHER_CODE')->references('VOUCHER_CODE')->on('VOUCHER');
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
