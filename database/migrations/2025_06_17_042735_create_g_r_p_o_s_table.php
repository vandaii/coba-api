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
            $table->foreignId('po_id')->constrained()->onDelete('cascade');
            $table->date('receive_date');
            $table->string('expense_type')->default('Inventory');
            $table->string('shipper_name');
            $table->string('receive_name');
            $table->string('supplier');
            $table->string('packing_slip');
            $table->text('notes');
            $table->timestamps();
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
