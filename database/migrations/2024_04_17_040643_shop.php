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
        Schema::dropIfExists('SHOP');
        Schema::create('SHOP', function (Blueprint $table) {
            $table->bigIncrements("SHOP_ID");
            $table->string('SHOP_NAME');
            $table->string('PHONE');
            $table->string('AVT_IMAGE_ID');
            $table->string('COVER_IMAGE_ID');
            $table->unsignedBigInteger('SHOP_OWNER_ID');
            $table->integer('ADDRESS_ID');
            $table->longText('DESCRIPTION');
            $table->boolean('IS_DELETED');

            $table->foreign('AVT_IMAGE_ID')->references('IMAGE_ID')->on('IMAGE');
            $table->foreign('COVER_IMAGE_ID')->references('IMAGE_ID')->on('IMAGE');
            $table->foreign('SHOP_OWNER_ID')->references('USER_ID')->on('USER');
            
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('SHOP');
    }
};
