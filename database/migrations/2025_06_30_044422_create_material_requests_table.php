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
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->date('request_date');
            $table->date('due_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending'); //Pending, Approve Accounting, Approve Area manager, Draft
            $table->boolean('approve_area_manager')->default(false);
            $table->boolean('approve_accounting')->default(false);
            $table->text('remark_revision')->nullable();
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
        Schema::dropIfExists('material_requests');
    }
};
