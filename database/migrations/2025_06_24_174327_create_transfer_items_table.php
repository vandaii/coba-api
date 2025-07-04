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
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->decimal('quantity');
            $table->string('unit');
            $table->timestamps();

            $table->string('transfer_out_number');
            $table->string('transfer_in_number')->nullable();
            $table->foreign('transfer_out_number')->references('transfer_out_number')->on('transfer_outs');
            $table->foreign('transfer_in_number')->references('transfer_in_number')->on('transfer_ins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};
