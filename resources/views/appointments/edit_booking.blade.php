@php
    use Carbon\Carbon;
@endphp
<x-app-layout>

<html>
    <head>
        <title>Edit Booking</title>
        <script src="{{version('js/dist/jquery.min.js')}}"></script>
        <script src="{{version('js/dist/sweetalert2.all.min.js')}}"></script>
        <link rel="stylesheet" href="{{version('css/main.css')}}">
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2><b>Edit Booking Slots</b></h2>
                        <br>
                        <p><b>Appointment:</b> {{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('booking.edit-booking', $appointment->id) }}" method="POST" id="edit-book-form">
                            @csrf
                            @method('PUT')
                            </br>

                            <div class="form-group">
                                <label for="slots">Number of Guests</label>
                                <input type="text" name="slots" id="slots" value="{{ $apptUserSlots }}" class="form-control">
                            </div>

                            <button type="submit" class="grn-btn">Update</button>
                        </form>
                        <form action="{{ route('booking.cancel-booking', $appointment->id) }}" method="POST" id="cancel-form">
                            @csrf
                            @method('POST')
                            <div class="form-group" style="text-align:center">
                                <label for="cancel">Do you want to cancel your booking?</label>
                                <button type="submit" class="red-btn">Cancel</button>
                            </div>
                        </form>

                        <script>
                            var slotsLeft = {{$availableSlots}}
                            var userSlots = {{$userSlots}}
                            var aUS = {{$apptUserSlots}}
                            var startTime = new Date("{{$appointment->start_time}}")
                            var MAX_SLOTS_PER_USER = {{$organization->max_slots_per_user}}
                            var registrationOpen = {{$organization->registration_open}}
                        </script>
                        <script type="module" src="{{ version('js/appt/book.js') }}"></script>
                        <script type="module" src="{{ version('js/appt/cancelbooking.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
