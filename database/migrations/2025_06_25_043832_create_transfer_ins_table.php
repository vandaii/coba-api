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
        Schema::create('transfer_ins', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_in_number')->unique();
            $table->string('transfer_out_number');
            $table->date('receipt_date');
            $table->date('transfer_date');
            $table->unsignedBigInteger('source_location_id');
            $table->unsignedBigInteger('destination_location_id');
            $table->string('receive_name');
            $table->string('delivery_note')->nullable();
            $table->text('notes')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->foreign('transfer_out_number')->references('transfer_out_number')->on('transfer_outs');
            $table->foreign('source_location_id')->references('id')->on('store_locations');
            $table->foreign('destination_location_id')->references('id')->on('store_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_ins');
    }
};
