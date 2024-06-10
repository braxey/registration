<x-app-layout>
    <html>
        <head>
            <title>{{ __('User Bookings - WTB Registration') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/AdminBooking.js')) }}"></script>
        </head>
        <body>
            <div id="user_bookings_landing_container" class="flex justify-center">
                
                <div class="container" >
                    <form id="user_bookings_lookup_form" action="{{ route('admin-booking.lookup') }}">
                        <div class="filter-container flex justify-center mb-6 mt-6">
                            <div id="filter-inputs-container" class="form-container togglers">
                                <p class="mb-4 text-lg">{{ __('Enter a user\'s name:') }}</p>
                                <div class="form-group">
                                    <label for="name"></label>
                                    <div class="flex">
                                        <input type="text" name="name" id="name" class="form-control mr-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="tab-container appointment-table" id="user_bookings_results_container"></div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>