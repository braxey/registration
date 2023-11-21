@php
    use Carbon\Carbon;
@endphp
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
                        <h2><b>Edit Booking Slots</b></h2>
                        <br>
                        <p><b>Appointment:</b> {{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</p>

                        <form action="{{ route('admin-booking.edit-booking', ['userId' => $user->id, 'appointmentId' => $appointment->id]) }}" method="POST" id="edit-book-form">
                            @csrf
                            @method('PUT')
                            </br>

                            <div class="form-group">
                                <label for="slots">Number of Guests</label>
                                <input type="text" name="slots" id="slots" value="{{ $apptUserSlots }}" class="form-control">
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
        var slotsLeft = {{$availableSlots}}
        var userSlots = {{$userSlots}}
        var apptUserSlots = {{$apptUserSlots}}
        var startTime = new Date("{{$appointment->start_time}}")
        var MAX_SLOTS_PER_USER = {{$organization->max_slots_per_user}}

        const MAX_CHAR_PER_STRING = 4

        $(function(){
            let form = document.getElementById('edit-book-form')
            // if appt user slots isnt defined, set to 0
            form.addEventListener('submit', function(e){
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
                if (slotsRequested - apptUserSlots > slotsLeft) {
                    let left = Math.max(slotsLeft, 0)
                    errorPop('Error', left>0 ? 'The requested number of slots is not available. There are only '+slotsLeft+' open slots for this time.'
                                            : 'There are no slots remaining for this appointment.')
                    return false
                // make sure the user doesn't surpass the max allowed per user
                }else if(slotsRequested+userSlots-apptUserSlots > MAX_SLOTS_PER_USER){
                    errorPop('Error', 'You can only book '+MAX_SLOTS_PER_USER+' slots at a time. ' 
                        + ((userSlots > 0) 
                            ? 'You already have '+userSlots+' slots booked.' 
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
                        form.submit()
                    }
                })
            })

            let cancel_form = document.getElementById('cancel-form')
            try{
                cancel_form.addEventListener('submit', function(e){
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
                        // If the user confirms, submit the form
                        if (result.isConfirmed) {
                            cancel_form.submit()
                        }
                    })
                })
            }catch(e){
                // no appointments to cancel
            }
        })

        function isInteger(str) {
            if (typeof str != "string") return false // we only process strings!  
            return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
                !isNaN(parseFloat(str)) && // ...and ensure strings of whitespace fail
                !str.includes('.') // don't allow floats
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
