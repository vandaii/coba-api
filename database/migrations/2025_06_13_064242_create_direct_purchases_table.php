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
        Schema::create('direct_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('no_direct_purchase')->nullable(false)->unique();
            $table->string('supplier')->nullable(false);
            $table->date('date')->nullable(false);
            $table->string('expense_type')->nullable(false)->default('Inventory');
            $table->float('total_amount')->nullable();
            $table->string('purchase_proof')->nullable();
            $table->string('note')->nullable();
            $table->string('status')->default('Pending'); //Pending, Approved Area Manager, Approved, Draft
            $table->boolean('approve_area_manager')->default(false);
            $table->boolean('approve_accounting')->default(false);
            $table->text('remark_revision')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_purchases');
    }
};
