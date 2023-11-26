<x-app-layout>

<html>
    <head>
        <title>Edit Booking</title>
        <script type="text/javascript" src="{{ version('js/dist/jquery.min.js') }}"></script>
        <script type="text/javascript" src="{{ version('js/dist/sweetalert2.all.min.js') }}"></script>
        <link rel="stylesheet" href="{{version('css/main.css') }}">
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2><b>Edit Booking Slots</b></h2>
                        <br>
                        <p><b>Appointment:</b> {{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('admin-booking.edit-booking', ['userId' => $user->getId(), 'appointmentId' => $appointment->getId()]) }}" method="POST" id="edit-book-form">
                            @csrf
                            @method('PUT')
                            </br>

                            <div class="form-group">
                                <label for="slots">Number of Guests</label>
                                <input type="text" name="slots" id="slots" value="{{ $bookingSlots }}" class="form-control">
                            </div>

                            <button type="submit" class="grn-btn">Update</button>
                        </form>
                        <form action="{{ route('admin-booking.cancel-booking', ['userId' => $user->id, 'appointmentId' => $appointment->id]) }}" method="POST" id="cancel-form">
                            @csrf
                            @method('POST')
                            <div class="form-group" style="text-align:center">
                                <label for="cancel">Do you want to cancel the booking?</label>
                                <button type="submit" class="red-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>
        // TODO: Make this script its own file.
        var slotsLeft = {{ $availableSlots }}
        var userSlots = {{ $userSlots }}
        var bookingSlots = {{ $bookingSlots }}
        var startTime = new Date("{{ $appointment->getStartTime() }}")
        var MAX_SLOTS_PER_USER = {{ $organization->getMaxSlotsPerUser() }}

        const MAX_CHAR_PER_STRING = 4

        $(function() {
            let form = $('#edit-book-form')
            form.on('submit', function (e) {
                let slotsRequested = $('#slots').val().toString()
                e.preventDefault()

                // make sure the input is an integer and the number isn't unreasonably big
                if (!isInteger(slotsRequested) || slotsRequested.length >= MAX_CHAR_PER_STRING){
                    errorPop('Error', 'Must enter a valid number.')
                    return false
                }

                // make sure the number is above 0
                slotsRequested = parseInt(slotsRequested)
                if (slotsRequested <= 0){
                    errorPop('Error', 'The number must be above 0.')
                    return false
                }

                // make sure there are enough slots for the appt to allot
                if (slotsRequested - bookingSlots > slotsLeft) {
                    let left = Math.max(slotsLeft, 0)
                    errorPop('Error', left > 0 
                        ? 'The requested number of slots is not available. There are only ' + slotsLeft + ' open slots for this time.'
                        : 'There are no slots remaining for this appointment.')
                    return false
                // make sure the user doesn't surpass the max allowed per user
                } else if (slotsRequested + userSlots - bookingSlots > MAX_SLOTS_PER_USER){
                    errorPop('Error', 'You can only book ' + MAX_SLOTS_PER_USER + ' slots at a time. ' 
                        + ((userSlots > 0) 
                            ? 'You already have ' + userSlots + ' slots booked.' 
                            : 'You currently have no bookings.'))
                    return false
                }

                // form submission
                Swal.fire({
                    title: 'Confirmation',
                    text: (slotsRequested == 0) 
                        ? 'Are you sure you want to cancel the appointment?'
                        : 'Are you sure you want to update the booking to '+ slotsRequested + ' slots?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    confirmButtonColor: "#088708"
                }).then((result) => {
                    // If the user confirms, submit the form
                    if (result.isConfirmed) {
                        form.off('submit')
                        form.submit()
                    }
                })
            })

            let cancelForm = $('#cancel-form')
            try{
                cancelForm.on('submit', function (e) {
                    e.preventDefault()

                    // sweetalert for confirmation
                    Swal.fire({
                        title: 'Confirmation',
                        text: 'Are you sure you want to cancel the appointment?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        confirmButtonColor: "#088708"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            cancelForm.off('submit')
                            cancelForm.submit()
                        }
                    })
                })
            } catch (e) {
                // no appointments to cancel
            }
        })

        function isInteger(str) {
            if (typeof str != "string") return false
            return !isNaN(str) &&
                !isNaN(parseFloat(str)) &&
                !str.includes('.')
        }

        function errorPop(_title, _text){
            Swal.fire({
                title: _title,
                text: _text,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: "#088708"
            })
        }
    </script>
</html>
</x-app-layout>
