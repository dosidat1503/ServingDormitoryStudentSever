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
            //những thuộc tính quyết định số tiền và % giảm giá
            $table->integer('DISCOUNT_VALUE');
            $table->integer('MAX_QUANTITY'); 
            $table->integer('MAX_DISCOUNT_VALUE')->nullable();//số tiền giảm tối đa, đối với %, giảm giá cố định thì có thể để null
            //những thuộc tính cần kiểm tra điều kiện
            $table->string('VOUCHER_CODE', 191)->unique();
            $table->unsignedBigInteger('SHOP_ID');
            $table->integer('MIN_ORDER_TOTAL')->nullable();//tổng tiền đơn hàng tối thiểu để sử dụng voucher
            $table->date('START_DATE');
            $table->date('EXPIRATION_DATE');
            $table->integer('REMAIN_AMOUNT');//số lượng còn lại 

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
