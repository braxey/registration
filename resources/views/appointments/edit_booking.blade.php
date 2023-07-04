<x-app-layout>

<html>
    <head>
        <title>Edit Booking</title>
        <script src="{{asset('js/dist/jquery.min.js')}}"></script>
        <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
        <link rel="stylesheet" href="{{asset('css/main.css')}}">
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2>Edit Booking Slots</h2>

                        <form action="{{ route('appointment.editbooking', $appointment->id) }}" method="POST" id="edit-book-form">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="slots">Slots</label>
                                <input type="text" name="slots" id="slots" value="{{ $apptUserSlots }}" class="form-control">
                            </div>

                            <button type="submit" class="red-btn">Update</button>
                        </form>
                        <form action="{{ route('appointment.cancelbooking', $appointment->id) }}" method="POST" id="cancel-form">
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
                        </script>
                        <script type="module" src="{{ asset('js/appt/book.js') }}"></script>
                        <script type="module" src="{{ asset('js/appt/cancelbooking.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
