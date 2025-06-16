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
        Schema::create('direct_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direct_purchase_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->string('item_description');
            $table->integer('quantity');
            $table->decimal('price', 20, 2);
            $table->decimal('total_price', 20, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_purchase_items');
    }
};
