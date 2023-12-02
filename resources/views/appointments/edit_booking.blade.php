<x-app-layout>

<html>
    <head>
        <title>Edit Booking</title>
        <script type="text/javascript" src="{{ version('js/dist/jquery.min.js') }}"></script>
        <script type="text/javascript" src="{{ version('js/dist/sweetalert2.all.min.js') }}"></script>
        <link rel="stylesheet" href="{{ version('css/main.css') }}">
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2><b>Edit Booking Slots</b></h2>
                        <br>
                        <p><b>Appointment:</b> {{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('booking.edit-booking', $appointment->getId()) }}" method="POST" id="edit-book-form">
                            @csrf
                            @method('PUT')
                            </br>

                            <div class="form-group">
                                <label for="slots">Number of Guests</label>
                                <input type="text" name="slots" id="slots" value="{{ $bookingSlots }}" class="form-control">
                            </div>

                            <button type="submit" class="grn-btn">Update</button>
                        </form>
                        <form action="{{ route('booking.cancel-booking', $appointment->getId()) }}" method="POST" id="cancel-form">
                            @csrf
                            @method('POST')
                            <div class="form-group" style="text-align:center">
                                <label for="cancel">Do you want to cancel your booking?</label>
                                <button type="submit" class="red-btn">Cancel</button>
                            </div>
                        </form>

                        <script>
                            var slotsLeft = {{ $availableSlots }}
                            var userSlots = {{ $userSlots }}
                            var aUS = {{ $bookingSlots }}
                            var startTime = new Date("{{ $appointment->getStartTime() }}")
                            var MAX_SLOTS_PER_USER = {{ $organization->getMaxSlotsPerUser() }}
                            var registrationOpen = {{ $organization->registrationIsOpen() }}
                        </script>
                        <script type="text/javascript" src="{{ version('js/appt/book.js') }}"></script>
                        <script type="text/javascript" src="{{ version('js/appt/cancelbooking.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
