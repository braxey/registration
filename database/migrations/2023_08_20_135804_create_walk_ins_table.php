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
        Schema::create('walk_ins', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); // Created at and Updated at columns

            $table->string('email');
            $table->string('name');
            $table->integer('slots');
            $table->dateTime('desired_time');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->boolean('notified')->default(false);

            // Foreign key constraint for appointment_id referencing the appointments table
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walk_ins');
    }
};
