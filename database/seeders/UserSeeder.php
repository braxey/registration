<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => 'Bradley',
            'last_name' => 'Johnson',
            'email' => 'bradleyjohnson0009@gmail.com',
            'email_verified_at' => now('EST'),
            'password' => Hash::make('gilgamesh'),
            'admin' => 1
        ]);
    }
}
