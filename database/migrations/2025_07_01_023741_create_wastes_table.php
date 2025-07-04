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
        Schema::create('wastes', function (Blueprint $table) {
            $table->id();
            $table->string('doc_number')->unique();
            $table->date('waste_date');
            $table->string('prepared_by');
            $table->boolean('approve_area_manager')->default(false);
            $table->boolean('approve_accounting')->default(false);
            $table->string('waste_proof')->nullable();
            $table->text('remark')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->unsignedBigInteger('store_location');
            $table->foreign('store_location')->references('id')->on('store_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wastes');
    }
};
