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
        Schema::create('g_r_p_o_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->integer('quantity');
            $table->string('unit')->default('PCS'); //PCS, KG, Box, dll.
            $table->timestamps();

            $table->string('item_code');
            $table->foreign('item_code')->references('item_code')->on('items');
            $table->string('grpo_number');
            $table->foreign('grpo_number')->references('grpo_number')->on('g_r_p_o_s');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g_r_p_o_items');
    }
};
