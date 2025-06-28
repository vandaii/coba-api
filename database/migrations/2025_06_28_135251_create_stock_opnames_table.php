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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('stock_opname_number')->unique();
            $table->date('stock_opname_date');
            $table->date('input_stock_date'); //Tanggal user mengisi data
            $table->string('counted_by'); //penghitung stock saat sampai
            $table->string('prepared_by');
            $table->string('status'); //On Going, Completed
            $table->timestamps();

            $table->unsignedBigInteger('store_location');
            $table->foreign('store_location')->references('id')->on('store_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
