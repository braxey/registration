@php
    use Carbon\Carbon;
@endphp
<x-app-layout>
<html>
    <head>
        <title>Book</title>
        <script src="{{asset('js/dist/jquery.min.js')}}"></script>
        <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
        <link rel="stylesheet" href="{{asset('css/main.css')}}">
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2><b>Book Slots</b></h2>
                        <br>
                        <p><b>Appointment:</b> {{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('booking.book', $appointment) }}" method="POST" id="book-form">
                            @csrf
                            </br>

                            <div class="form-group">
                                <label for="slots">Number of Guests</label>
                                <input type="text" name="slots" id="slots" class="form-control">
                            </div>
                            <button class="grn-btn" type="submit">Book</button>
                        </form>
                        <script>
                            var slotsLeft = {{$availableSlots}}
                            var userSlots = {{$userSlots}}
                            var startTime = new Date("{{$appointment->start_time}}")
                            var MAX_SLOTS_PER_USER = {{$organization->max_slots_per_user}}
                            var registrationOpen = {{$organization->registration_open}}
                        </script>
                        <script type="module" src="{{ asset('js/appt/book.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
