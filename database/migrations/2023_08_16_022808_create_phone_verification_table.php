<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneVerificationTable extends Migration
{
    public function up()
    {
        Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->timestamp('time_sent');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phone_verifications');
    }
}

