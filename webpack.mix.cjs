const mix = require('laravel-mix');

/*
|--------------------------------------------------------------------------
| Mix Asset Management
|--------------------------------------------------------------------------
|
| Mix provides a clean, fluent API for defining some Webpack build steps
| for your Laravel applications. By default, we are compiling the CSS
| file for the application as well as bundling up all the JS files. 
|
*/

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/authentication/ResetPassword.js', 'public/js')
    .js('resources/js/organizations/ModifyOrganization.js', 'public/js')
    .js('resources/js/booking/Booking.js', 'public/js')
    .js('resources/js/dashboards/UserDashboard.js', 'public/js')
    .js('resources/js/appointments/Appointment.js', 'public/js')
    .js('resources/js/walk-ins/WalkIn.js', 'public/js')
    .sass('resources/sass/main.scss', 'public/css')
    .postCss('resources/css/app.css', 'public/css', [
       //
    ]);
