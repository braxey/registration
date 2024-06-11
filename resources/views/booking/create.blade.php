<x-app-layout>
<html>
    <head>
        <title>{{ __('Book') }}</title>
        <script type="text/javascript" src="{{ version(mix('js/Booking.js')) }}"></script>
        <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
    </head>
    <body>
        <div id="create_booking_container" class="flex justify-center text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2><b>{{ __('Book Slots') }}</b></h2>
                        <br>
                        <p><b>{{ __('Appointment:') }}</b> {{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('booking.book', $appointment) }}" method="POST" id="create_booking_form">
                            @csrf
                            </br>

                            <div class="form-group">
                                <label for="slots">{{ __('Number of Guests') }}</label>
                                <input type="text" name="slots" id="slots" class="form-control">
                            </div>
                            <button class="grn-btn" type="submit">{{ __('Book') }}</button>
                        </form>

                        <input id="slots_remaining" type="hidden" value="{{ $availableSlots }}" />
                        <input id="current_number_of_slots" type="hidden" value="{{ $userSlots }}" />
                        <input id="max_slots" type="hidden" value="{{ $organization->getMaxSlotsPerUser() }}" />
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
