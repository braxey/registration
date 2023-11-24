<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\GuestlistController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\WalkinWaitlistController;
// use App\Http\Controllers\TwilioController;

use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\Organization;

use App\Mail\VerificationEmail;

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
    return redirect('/register');
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
// Route::post('/forgot-password-number', [PasswordResetController::class, 'verifyNumber'])->name('forgot-password.check');
Route::post('/forgot-password-email', [PasswordResetController::class, 'verifyEmail'])->name('forgot-password.check');
// Route::get('/reset-verify-number', [PasswordResetController::class, 'getNumberVerifyForm'])->name('forgot-password.verify-form');
Route::get('/reset-verify-email', [PasswordResetController::class, 'getEmailVerifyForm'])->name('forgot-password.verify-form');
Route::post('/reset-verify', [PasswordResetController::class, 'verify'])->name('forgot-password.verify-token');
Route::post('/reset-resend-verify-token', [PasswordResetController::class, 'resend'])->name('forgot-password.resend-verify-token');
Route::get('/forgot-password-reset', [PasswordResetController::class, 'getResetPasswordForm'])->name('forgot-password.reset-form');
Route::post('/forgot-password-update', [PasswordResetController::class, 'updatePassword'])->name('forgot-password.update');

// Route::middleware(['twilio.webhook'])->group(function () {
//     Route::post('/incoming-sms', [TwilioController::class, 'handleIncomingSMS'])->name('twilio.incoming_sms');
// });

/**
 * Booking
 */
Route::middleware(['auth', 'booking'])->group(function () {
    Route::prefix('appointments/{appointmentId}')->group(function () {
        Route::get('/book', [BookingController::class, 'getBookingPage'])->name('booking.get-booking');
        Route::get('/edit-booking', [BookingController::class, 'getEditBookingPage'])->name('booking.get-edit-booking');

        Route::post('/book', [BookingController::class, 'book'])->name('booking.book');
        Route::put('/edit-booking', [BookingController::class, 'editBooking'])->name('booking.edit-booking');
        Route::post('/cancel-booking', [BookingController::class, 'cancelBooking'])->name('booking.cancel-booking');
    });
});

Route::middleware(['auth', 'admin'])->group(function() {
    /**
     * Guestlist
     */
    Route::prefix('guestlist')->group(function () {
        Route::get('/', [GuestlistController::class, 'getGuestlist'])->name('guestlist');
        Route::post('/update', [GuestlistController::class, 'updateGuestlist'])->name('guestlist.update');
    });


    /**
     * Organization
     */
    Route::prefix('organization/{organizationId}')->group(function () {
        Route::get('/edit', [OrganizationController::class, 'getEditPage'])->name('organization.get-edit');
        Route::put('/edit', [OrganizationController::class, 'update'])->name('organization.update');
        Route::post('/toggle-registration', [OrganizationController::class, 'toggleRegistration'])->name('organization.toggle-registration');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointment.edit');
    Route::put('/appointments/{id}/edit', [AppointmentController::class, 'edit'])->name('appointment.update');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointment.create_form');
    Route::post('/appointments/create', [AppointmentController::class, 'create'])->name('appointment.create');
    Route::post('/appointments/{id}/delete', [AppointmentController::class, 'delete'])->name('appointment.delete');

    // Route::prefix('verify-phone')->group(function () {
    //     Route::get('/verify', [PhoneVerificationController::class, 'getVerifyForm'])->name('get-verify');
    //     Route::post('/verify', [PhoneVerificationController::class, 'verify'])->name('verify-phone-token');
    //     Route::post('/resend-verify-token', [PhoneVerificationController::class, 'resend'])->name('resend-verify-token');
    //     Route::get('/change-phone', [PhoneVerificationController::class, 'getChangePhoneForm'])->name('get-change-phone');
    //     Route::post('/change-phone', [PhoneVerificationController::class, 'changePhone'])->name('change-phone');
    // });
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
        Route::post('/{walkInId}/{apptId}/unlink-appt', [WalkinWaitlistController::class, 'unlinkAppointment'])->name('walk-in.unlink-appt-post');
    });

    Route::prefix('admin-user')->group(function () {
        Route::get('/', [AdminBookingController::class, 'getAdminUserLookupPage'])->name('admin-booking.lookup');
        Route::get('/{userId}', [AdminBookingController::class, 'getUsersUpcomingBookings'])->name('admin-booking.user');
        Route::get('/{userId}/{appointmentId}', [AdminBookingController::class, 'getBookingForUser'])->name('admin-booking.user-booking');
        Route::put('/{userId}/{appointmentId}/edit', [AdminBookingController::class, 'editBookingForUser'])->name('admin-booking.edit-booking');
        Route::post('/{userId}/{appointmentId}/cancel', [AdminBookingController::class, 'cancelBookingForUser'])->name('admin-booking.cancel-booking');
    });
});
