<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $eventName = "Walk Thru Bethlehem";
    $eventDescription = "Description";

    return view('welcome', 
        ['eventName' => $eventName,
        'eventDescription' => $eventDescription]);
})->name('welcome');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        // Get the authenticated user
        $user = Auth::user();

        // Retrieve the IDs of past and upcoming appointments associated with the user
        $pastAppointmentIds = AppointmentUser::where('user_id', $user->id)
            ->whereHas('appointment', function ($query) {
                $query->where('past_end', true);
            })
            ->pluck('appointment_id');

        $upcomingAppointmentIds = AppointmentUser::where('user_id', $user->id)
            ->whereHas('appointment', function ($query) {
                $query->where('past_end', false);
            })
            ->pluck('appointment_id');

        // Retrieve the past and upcoming appointments
        $pastAppointments = Appointment::whereIn('id', $pastAppointmentIds)->get();
        $upcomingAppointments = Appointment::whereIn('id', $upcomingAppointmentIds)->get();

        return view('dashboard', compact('pastAppointments', 'upcomingAppointments'));
    })->name('dashboard');
});

// Route accessible to all users, without any middleware
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');


Route::middleware(['auth'])->group(function () {
    Route::match(['GET', 'POST'], '/appointments/{id}/book', [AppointmentController::class, 'book'])->name('appointment.book');
    Route::get('/appointment/confirmation', [AppointmentController::class, 'confirmation'])->name('appointment.confirmation');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointment.edit');
    Route::put('/appointments/{id}', [AppointmentController::class, 'update'])->name('appointment.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointment.create_form');
    Route::post('/appointments/create', [AppointmentController::class, 'create'])->name('appointment.create');
});

Route::middleware(['auth'])->group(function(){
    Route::post('/appointments/{id}/delete', [AppointmentController::class, 'delete'])->name('appointment.delete');
});
    


