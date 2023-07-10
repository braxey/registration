<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(){
        Schema::create('appointment_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedInteger('slots_taken')->default(0);
            $table->unsignedInteger('showed_up')->default(0);
            $table->boolean('notified')->default(false);
            $table->index(['user_id', 'appointment_id']);
        });
    }

    public function down(){
        Schema::dropIfExists('appointment_user');
    }
};