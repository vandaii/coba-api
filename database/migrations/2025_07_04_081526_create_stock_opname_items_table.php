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
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('UoM');
            $table->timestamps();

            $table->string('item_code');
            $table->foreign('item_code')->references('item_code')->on('items');
            $table->string('stock_opname_number');
            $table->foreign('stock_opname_number')->references('stock_opname_number')->on('stock_opnames');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
