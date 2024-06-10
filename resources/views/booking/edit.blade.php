<x-app-layout>

<html>
    <head>
        <title>{{ __('Edit Booking') }}</title>
        <script type="text/javascript" src="{{ version(mix('js/Booking.js')) }}"></script>
        <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
    </head>
    <body>
        <div id="edit_booking_container" class="flex justify-center text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2><b>{{ __('Edit Booking Slots') }}</b></h2>
                        <br>
                        <p><b>{{ __('Appointment:') }}</b> {{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('booking.edit-booking', $appointment->getId()) }}" method="POST" id="edit_booking_form">
                            @csrf
                            @method('PUT')
                            </br>

                            <div class="form-group">
                                <label for="slots">{{ __('Number of Guests') }}</label>
                                <input type="text" name="slots" id="slots" value="{{ $bookingSlots }}" class="form-control">
                            </div>

                            <button type="submit" class="grn-btn">{{ __('Update') }}</button>
                        </form>
                        <form action="{{ route('booking.cancel-booking', $appointment->getId()) }}" method="POST" id="cancel_booking_form">
                            @csrf
                            @method('POST')
                            <div class="form-group" style="text-align:center">
                                <label for="cancel">{{ __('Do you want to cancel your booking?') }}</label>
                                <button type="submit" class="red-btn">{{ __('Cancel') }}</button>

                            </div>
                        </form>

                        <input id="slots_remaining" type="hidden" value="{{ $availableSlots }}" />
                        <input id="current_number_of_slots" type="hidden" value="{{ $userSlots }}" />
                        <input id="slots_for_appointment" type="hidden" value="{{ $bookingSlots }}" />
                        <input id="max_slots" type="hidden" value="{{ $organization->getMaxSlotsPerUser() }}" />
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
