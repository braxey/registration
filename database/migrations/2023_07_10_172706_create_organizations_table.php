<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('org_name');
            $table->unsignedInteger('max_slots_per_user')->default(0);
            $table->boolean('registration_open')->default(false);
            $table->timestamps();
        });

        // Insert a row with id=1, org_name='KABC', max_slots_per_user = 6, registration_open=0
        DB::table('organizations')->insert([
            'id' => 1,
            'org_name' => 'KABC',
            'max_slots_per_user' => 6,
            'registration_open' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
