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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->string('quantity');
            $table->string('unit')->nullable(); //pcs, box, kg
            $table->string('UoM')->nullable(); //gram dll
            $table->string('notes')->nullable();
            $table->string('no_grpo')->nullable();
            $table->string('stock_opname_number')->nullable();
            $table->string('request_number')->nullable();
            $table->string('doc_number')->nullable();

            $table->foreign('no_grpo')->references('no_grpo')->on('g_r_p_o_s');
            $table->foreign('stock_opname_number')->references('stock_opname_number')->on('stock_opnames');
            $table->foreign('request_number')->references('request_number')->on('material_requests');
            $table->foreign('doc_number')->references('doc_number')->on('wastes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
