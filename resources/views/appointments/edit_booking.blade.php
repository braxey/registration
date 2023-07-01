<x-app-layout>

<html>
<head>
    <script src="{{asset('js/dist/jquery.min.js')}}"></script>
    <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
</head>
<body>

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
            <script>
                var slotsLeft = "<?php echo $availableSlots;?>"
                var userSlots = parseInt("<?php echo $userSlots;?>")
                var apptUserSlots = parseInt("<?php echo $apptUserSlots;?>")
                var startTime = new Date("<?php echo $appointment->start_time;?>")
            </script>
            <script type="module" src="{{ asset('js/appt/book.js') }}"></script>
        </div>
    </div>
</div>
</body>
</html>
</x-app-layout>
