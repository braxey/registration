<x-app-layout>

<html>
    <head>
        <script src="{{asset('js/dist/jquery.min.js')}}"></script>
        <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2>Edit Booking Slots</h2>

                        <form action="{{ route('appointment.editbooking', $appointment) }}" method="POST" id="edit-book-form">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="slots">Slots</label>
                                <input type="text" name="slots" id="slots" value="{{ $apptUserSlots }}">
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                        <form action="{{ route('appointment.cancelbooking', $appointment->id) }}" method="POST" id="cancel-form">
                            @csrf
                            @method('POST')
                            <button type="submit">Cancel</button>
                        </form>
                        <!-- <input type="hidden" id="availableSlots" value="{{ $availableSlots }}">
                        <input type="hidden" id="userSlots" value="userSlots">
                        <input type="hidden" id="apptUserSlots" value="apptUserSlots">
                        <input type="hidden" id="hidden_field" value="some value"> -->

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
