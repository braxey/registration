<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

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
        return view('dashboard');
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
    


