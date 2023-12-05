<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestlistController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\WalkInController;
use App\Http\Controllers\LinkingController;
use App\Http\Controllers\MassMailerController;

Route::get('/', function () {
    return redirect('/register');
})->name('welcome');

/**
 * Dashboards
 */
Route::get('/dashboard', [DashboardController::class, 'getDashboard'])
    ->middleware(['auth:sanctum', config('jetstream.auth_session')])->name('dashboard');
Route::get('/appointments', [DashboardController::class, 'showAllAppointments'])->name('appointments.index');

/**
 * Forgot Password
 */
Route::prefix('forgot-password')->group(function () {
    Route::get('/', [PasswordResetController::class, 'getForgotPasswordPage'])->name('get-forgot-password');
    Route::get('/verify-email', [PasswordResetController::class, 'getVerifyEmailPage'])->name('forgot-password.get-verify-email');
    Route::get('/reset', [PasswordResetController::class, 'getResetPasswordPage'])->name('forgot-password.get-reset');

    Route::post('/check-email', [PasswordResetController::class, 'verifyEmail'])->name('forgot-password.check-email');
    Route::post('/verify-token', [PasswordResetController::class, 'verifyToken'])->name('forgot-password.verify-token');
    Route::post('/resend-token', [PasswordResetController::class, 'resendToken'])->name('forgot-password.resend-token');
    Route::post('/update-password', [PasswordResetController::class, 'updatePassword'])->name('forgot-password.update-password');
});

Route::middleware('auth')->group(function () {
    Route::middleware('admin')->group(function () {
        /**
         * Appointments
         */
        Route::prefix('appointments')->group(function () {
            Route::get('/create', [AppointmentController::class, 'getCreatePage'])->name('appointment.get-create');
            Route::post('/create', [AppointmentController::class, 'create'])->name('appointment.create');

            Route::prefix('{appointmentId}')->middleware('appointment')->group(function () {
                Route::get('/edit', [AppointmentController::class, 'getEditPage'])->name('appointment.get-edit');
                Route::put('/update', [AppointmentController::class, 'update'])->name('appointment.update');
                Route::post('/delete', [AppointmentController::class, 'delete'])->name('appointment.delete');
            });
        });

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

        /**
         * Walk-ins
         */
        Route::prefix('walk-in')->group(function () {
            Route::get('/waitlist', [WalkInController::class, 'getWaitlist'])->name('walk-in.show-waitlist');
            Route::get('/create', [WalkInController::class, 'getCreateWalkinPage'])->name('walk-in.get-create');
            Route::post('/create', [WalkInController::class, 'createWalkin'])->name('walk-in.create');
            
            Route::prefix('{walkInId}')->middleware('walk-in')->group(function () {
                Route::get('/edit-walkin', [WalkInController::class, 'getEditWalkinPage'])->name('walk-in.get-edit');
                Route::put('/edit-walkin', [WalkInController::class, 'updateWalkin'])->name('walk-in.edit');
                Route::post('/delete', [WalkInController::class, 'deleteWalkin'])->name('walk-in.delete');
            });
        });

        /**
         * Link/Unlink Walk-ins to Appointments
         */
        Route::prefix('walk-in/{walkInId}')->middleware('walk-in')->group(function () {
            Route::get('/link-appointment', [LinkingController::class, 'getAppointmentLinkPage'])->name('walk-in.get-link-appointment');
            Route::post('/{appointmentId}/link-appt', [LinkingController::class, 'linkAppointment'])
                ->middleware('appointment')->name('walk-in.link-appointment');
            Route::post('/{appointmentId}/unlink-appointment', [LinkingController::class, 'unlinkAppointment'])
                ->middleware('appointment')->name('walk-in.unlink-appointment');
        });
    
        /**
         * Admin Booking Edit for User
         */
        Route::prefix('admin-user')->group(function () {
            Route::get('/', [AdminBookingController::class, 'getAdminUserLookupPage'])->name('admin-booking.lookup');
            Route::get('/{userId}', [AdminBookingController::class, 'getUsersUpcomingBookings'])->name('admin-booking.user');
            Route::get('/{userId}/{appointmentId}', [AdminBookingController::class, 'getBookingForUser'])->name('admin-booking.user-booking');
            Route::put('/{userId}/{appointmentId}/edit', [AdminBookingController::class, 'editBookingForUser'])->name('admin-booking.edit-booking');
            Route::post('/{userId}/{appointmentId}/cancel', [AdminBookingController::class, 'cancelBookingForUser'])->name('admin-booking.cancel-booking');
        });

        Route::middleware('gilgamesh')->group(function () {
            /**
             * Mass Mailer
             */
            Route::prefix('mass-mailer')->group(function () {
                Route::get('/', [MassMailerController::class, 'getMassMailerPage'])->name('mass-mailer.landing');
                Route::post('/send', [MassMailerController::class, 'sendMassEmail'])->middleware(['timeout:14400'])->name('mass-mailer.send');
            });
        });
    });

    /**
     * Booking
     */
    Route::prefix('appointments/{appointmentId}')->middleware('booking')->group(function () {
        Route::get('/book', [BookingController::class, 'getBookingPage'])->name('booking.get-booking');
        Route::get('/edit-booking', [BookingController::class, 'getEditBookingPage'])->name('booking.get-edit-booking');

        Route::post('/book', [BookingController::class, 'book'])->name('booking.book');
        Route::put('/edit-booking', [BookingController::class, 'editBooking'])->name('booking.edit-booking');
        Route::post('/cancel-booking', [BookingController::class, 'cancelBooking'])->name('booking.cancel-booking');
    });
});
