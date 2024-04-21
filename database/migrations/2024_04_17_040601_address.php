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
            $table->string('DETAIL');
            $table->string('COMMUNE');
            $table->string('DISTRICT');
            $table->string('PROVINCE');
            $table->boolean('IS_DELETED');
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
