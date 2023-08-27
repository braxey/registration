<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\WalkinWaitlistController;
use App\Http\Controllers\TwilioController;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\Organization;
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
    return redirect(route('appointments.index'));
})->name('welcome');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', function () {
        // Get the authenticated user
        $user = Auth::user();
        // Get org
        $organization = Organization::findOrFail(1);

        $allAppointmentIds = AppointmentUser::where('user_id', $user->id)
            ->pluck('appointment_id');

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
        $allAppointments = Appointment::whereIn('id', $allAppointmentIds)
            ->orderByRaw("
                CASE
                    WHEN status = 'in progress' THEN 1
                    WHEN status = 'upcoming' THEN 2
                    WHEN status = 'completed' THEN 3
                    ELSE 3
                END
            ")
            ->orderByRaw("
                CASE WHEN status = 'completed' THEN start_time END DESC
            ")
            ->orderByRaw("
                CASE WHEN status = 'upcoming' THEN start_time END ASC
            ")
            ->orderByRaw("
                CASE WHEN status = 'in progress' THEN start_time END ASC
            ")
            ->get();
        $pastAppointments = Appointment::whereIn('id', $pastAppointmentIds)
            ->orderBy('start_time', 'desc')
            ->get();
        $upcomingAppointments = Appointment::whereIn('id', $upcomingAppointmentIds)
            ->orderByRaw("
                CASE WHEN status = 'upcoming' THEN start_time END ASC
            ")
            ->orderByRaw("
                CASE WHEN status = 'in progress' THEN start_time END ASC
            ")
            ->get();

        return view('dashboard', compact('allAppointments', 'pastAppointments', 'upcomingAppointments', 'organization'));
    })->name('dashboard');
});

// Route accessible to all users, without any middleware
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');

// Forgot password
Route::get('/forgot-password', [PasswordResetController::class, 'getForgotPasswordPage'])->name('forgot-password');
Route::post('/forgot-password-number', [PasswordResetController::class, 'verifyNumber'])->name('forgot-password.check');
Route::get('/reset-verify-number', [PasswordResetController::class, 'getNumberVerifyForm'])->name('forgot-password.verify-form');
Route::post('/reset-verify', [PasswordResetController::class, 'verify'])->name('forgot-password.verify-phone-token');
Route::post('/reset-resend-verify-token', [PasswordResetController::class, 'resend'])->name('forgot-password.resend-verify-token');
Route::get('/forgot-password-reset', [PasswordResetController::class, 'getResetPasswordForm'])->name('forgot-password.reset-form');
Route::post('/forgot-password-update', [PasswordResetController::class, 'updatePassword'])->name('forgot-password.update');

Route::middleware(['twilio.webhook'])->group(function () {
    Route::post('/incoming-sms', [TwilioController::class, 'handleIncomingSMS'])->name('twilio.incoming_sms');
});

Route::middleware(['auth', 'phone.verified'])->group(function () {
    Route::match(['GET', 'POST'], '/appointments/{id}/book', [AppointmentController::class, 'book'])->name('appointment.book');
    Route::match(['GET', 'PUT'], '/appointments/{id}/editbooking', [AppointmentController::class, 'edit_booking'])->name('appointment.editbooking');
    Route::post('/appointments/{id}/cancelbooking', [AppointmentController::class, 'cancel_booking'])->name('appointment.cancelbooking');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointment.edit');
    Route::put('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointment.update');
    Route::get('/guestlist', [AppointmentController::class, 'guestlist'])->name('appointments.guestlist');
    Route::post('/guestlist/update', [AppointmentController::class, 'update_guestlist'])->name('guestlist.update');
    Route::get('/organization/{id}/edit', [OrganizationController::class, 'edit'])->name('organization.edit_form');
    Route::put('/organization/{id}/edit', [OrganizationController::class, 'edit'])->name('organization.edit');
    Route::post('/organization/{id}/toggle', [OrganizationController::class, 'toggle_registration'])->name('organization.toggle_registration');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointment.create_form');
    Route::post('/appointments/create', [AppointmentController::class, 'create'])->name('appointment.create');
    Route::post('/appointments/{id}/delete', [AppointmentController::class, 'delete'])->name('appointment.delete');

    Route::prefix('verify-phone')->group(function () {
        Route::get('/verify', [PhoneVerificationController::class, 'getVerifyForm'])->name('get-verify');
        Route::post('/verify', [PhoneVerificationController::class, 'verify'])->name('verify-phone-token');
        Route::post('/resend-verify-token', [PhoneVerificationController::class, 'resend'])->name('resend-verify-token');
        Route::get('/change-phone', [PhoneVerificationController::class, 'getChangePhoneForm'])->name('get-change-phone');
        Route::post('/change-phone', [PhoneVerificationController::class, 'changePhone'])->name('change-phone');
    });
});
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('walkin-waitlist')->group(function () {
        Route::get('/', [WalkinWaitlistController::class, 'getWaitlist'])->name('walk-in.show-waitlist');
        Route::get('/create-walkin', [WalkinWaitlistController::class, 'getCreateWalkinForm'])->name('walk-in.create-form');
        Route::post('/create-walkin', [WalkinWaitlistController::class, 'createWalkin'])->name('walk-in.create');
        Route::get('/{id}/edit-walkin', [WalkinWaitlistController::class, 'getEditWalkinForm'])->name('walk-in.edit-form');
        Route::put('/{id}/edit-walkin', [WalkinWaitlistController::class, 'editWalkin'])->name('walk-in.edit');
        Route::post('/{id}/delete', [WalkinWaitlistController::class, 'deleteWalkin'])->name('walk-in.delete');
        Route::get('/{id}/link-appt', [WalkinWaitlistController::class, 'getApptLinkPage'])->name('walk-in.link-appt');
        Route::post('/{walkInId}/{apptId}/link-appt', [WalkinWaitlistController::class, 'linkAppointment'])->name('walk-in.link-appt-post');
    });
});
    