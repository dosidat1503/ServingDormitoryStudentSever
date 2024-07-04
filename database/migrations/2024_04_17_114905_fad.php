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
            $table->integer('QUANTITY')->default(0);
            $table->unsignedBigInteger('IMAGE_ID');
            $table->unsignedBigInteger('SHOP_ID');
            $table->integer('ID_PARENTFADOFTOPPING')->nullable();
            $table->integer('ID_PARENTFADOFSIZE')->nullable();
            $table->longText('DESCRIPTION')->nullable();
            $table->boolean('IS_DELETED');
            $table->integer('CATEGORY')->nullable();
            $table->integer('TAG')->nullable();
            $table->timestamp('DATE');

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
