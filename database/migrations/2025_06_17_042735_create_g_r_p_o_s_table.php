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
        Schema::create('g_r_p_o_s', function (Blueprint $table) {
            $table->id();
            $table->string('no_grpo')->unique();
            $table->string('no_po');
            $table->date('purchase_order_date');
            $table->date('receive_date');
            $table->string('expense_type')->default('Inventory');
            $table->string('shipper_name');
            $table->string('receive_name');
            $table->string('supplier');
            $table->string('packing_slip')->nullable();
            $table->text('notes')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->foreign('no_po')->references('no_purchase_order')->on('purchase_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g_r_p_o_s');
    }
};
