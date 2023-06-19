<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        Appointment::create([
            'title' => 'Appointment 1',
            'description' => 'Description for Appointment 1',
            'start_time' => now(),
            'end_time' => now()->addHours(1),
            'total_slots' => 5,
        ]);

        Appointment::create([
            'title' => 'Appointment 2',
            'description' => 'Description for Appointment 2',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'total_slots' => 10,
        ]);

        // Add more appointment records as needed

        $this->command->info('Appointments seeded successfully.');
    }
}
