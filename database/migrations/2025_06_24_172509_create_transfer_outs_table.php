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
        Schema::create('transfer_outs', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_out_number')->unique();
            $table->date('transfer_out_date');
            $table->unsignedBigInteger('source_location_id');
            $table->unsignedBigInteger('destination_location_id');
            $table->string('delivery_note')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->foreign('source_location_id')->references('id')->on('store_locations')->onDelete('cascade');
            $table->foreign('destination_location_id')->references('id')->on('store_locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_outs');
    }
};
