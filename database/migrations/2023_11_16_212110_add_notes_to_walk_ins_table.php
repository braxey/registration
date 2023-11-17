<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotesToWalkInsTable extends Migration
{
    public function up()
    {
        Schema::table('walk_ins', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::table('walk_ins', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}
