<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestlistController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\WalkinWaitlistController;

Route::get('/', function () {
    return redirect('/register');
})->name('welcome');

/**
 * Dashboards
 */
Route::get('/dashboard', [DashboardController::class, 'getDashboard'])
    ->middleware(['auth:sanctum', config('jetstream.auth_session')])->name('dashboard');
Route::get('/appointments', [DashboardController::class, 'showAllAppointments'])->name('appointments.index');

// Forgot password
Route::get('/forgot-password', [PasswordResetController::class, 'getForgotPasswordPage'])->name('forgot-password');
Route::post('/forgot-password-email', [PasswordResetController::class, 'verifyEmail'])->name('forgot-password.check');
Route::get('/reset-verify-email', [PasswordResetController::class, 'getEmailVerifyForm'])->name('forgot-password.verify-form');
Route::post('/reset-verify', [PasswordResetController::class, 'verify'])->name('forgot-password.verify-token');
Route::post('/reset-resend-verify-token', [PasswordResetController::class, 'resend'])->name('forgot-password.resend-verify-token');
Route::get('/forgot-password-reset', [PasswordResetController::class, 'getResetPasswordForm'])->name('forgot-password.reset-form');
Route::post('/forgot-password-update', [PasswordResetController::class, 'updatePassword'])->name('forgot-password.update');

Route::middleware(['auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
        /**
         * Appointments
         */
        Route::prefix('appointments')->group(function () {
            Route::get('/create', [AppointmentController::class, 'getCreatePage'])->name('appointment.get-create');
            Route::post('/create', [AppointmentController::class, 'create'])->name('appointment.create');

            Route::middleware(['appointment'])->group(function () {
                Route::get('/{appointmentId}/edit', [AppointmentController::class, 'getEditPage'])->name('appointment.get-edit');
                Route::put('/{appointmentId}/update', [AppointmentController::class, 'update'])->name('appointment.update');
                Route::post('/{appointmentId}/delete', [AppointmentController::class, 'delete'])->name('appointment.delete');
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
    });

    /**
     * Booking
     */
    Route::middleware(['booking'])->group(function () {
        Route::prefix('appointments/{appointmentId}')->group(function () {
            Route::get('/book', [BookingController::class, 'getBookingPage'])->name('booking.get-booking');
            Route::get('/edit-booking', [BookingController::class, 'getEditBookingPage'])->name('booking.get-edit-booking');
    
            Route::post('/book', [BookingController::class, 'book'])->name('booking.book');
            Route::put('/edit-booking', [BookingController::class, 'editBooking'])->name('booking.edit-booking');
            Route::post('/cancel-booking', [BookingController::class, 'cancelBooking'])->name('booking.cancel-booking');
        });
    });
});
