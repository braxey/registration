@php
    use Carbon\Carbon;
    use App\Models\AppointmentUser;
@endphp
<x-app-layout>
    <html>
        <head>
            <title>Appointments - WTB Registration</title>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                
                <div class="container" >
                    <h1 class="flex justify-center items-center h-screen" style="font-size: larger">Walk Thru Bethlehem Appointments</h1>

                    @if ($appointments->count() > 0)
                    <h3 class="flex justify-center items-center h-screen py-2 text-center">An appointment slot is needed for each person, including children.</h3>

                    <!-- Filter Form -->
                    <form id="filter-form" method="GET" action="{{ route('appointments.index') }}">
                        @csrf
                        @method('GET')
                        <div class="filter-container flex justify-center items-center h-screen mb-6 mt-6">
                            <div id="filter-inputs-container" class="form-container togglers">
                                <p class="mb-4 text-lg">Select your preferred time range:</p>
                                <div class="form-group">
                                    <label for="start_date_time">Start Date and Time</label>
                                    <div class="flex">
                                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control mr-2" min="{{$min}}" max="{{$max}}">
                                        <input type="time" name="start_time" id="start_time" value="{{ request('start_time') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="end_date_time">End Date and Time</label>
                                    <div class="flex">
                                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control mr-2" min="{{$min}}" max="{{$max}}">
                                        <input type="time" name="end_time" id="end_time" value="{{ request('end_time') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="button-container" style="justify-content: center;" id="filter-buttons">
                                    <button type="submit" class="grn-btn togglers" id="apply-filter">Apply</button>
                                    <button type="button" class="red-btn togglers" onclick="$('#start_date').val('');$('#start_time').val('');$('#end_date').val('');$('#end_time').val('');$('#apply-filter').click();">Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif

                    @if ($user && $user->admin)
                        <div class="flex">
                            <a class="flex justify-left h-screen grn-btn text-center" style="max-width: 125px; margin-left: 5px;" href="{{ route('appointment.get-create') }}">Create Appt</a>
                            <a class="flex justify-left h-screen red-btn text-center" style="max-width: 140px; margin-left: 5px;" href="{{ route('admin-booking.lookup') }}">User Bookings</a>
                        </div>
                    @endif

                    @if ($appointments->count() > 0)
                    <div class="tab-container appointment-table">
                        <table class="table mx-auto border border-slate-300 appt-pagination">  
                            <thead>
                                <tr class="border border-slate-300">
                                    <th class="border border-slate-300">Start Time</th>
                                    <th class="border border-slate-300">Slots Filled</th>
                                    <th class="border border-slate-300">Status</th>
                                    <th class="border border-slate-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appointments as $appointment)
                                    <tr class="border border-slate-300">
                                        <td class="border border-slate-300">{{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</td>
                                        <td class="border border-slate-300">{{ $appointment->slots_taken }} / {{ $appointment->total_slots }}</td>
                                        <td class="border border-slate-300">
                                            <span class="highlight text-white">{{ $appointment->status }}</span>
                                            {{ $appointment->isWalkInOnly() ? "W-I" : ""}}
                                        </td>
                                        <td class="border border-slate-300">
                                            <div class="table-buttons-cell">
                                            @if ($user && $user->admin)
                                                <a class="red-btn" href="{{ route('appointment.get-edit', $appointment->id) }}">Edit Appt</a>
                                            @endif
                                            @if (!$appointment->isOpen() || !$organization->registration_open || $appointment->isWalkInOnly())
                                                <a>Closed</a>  
                                            @elseif ($user?->id && AppointmentUser::where('user_id', $user->id)->where('appointment_id', $appointment->id)->exists())
                                                <a class="grn-btn" href="{{ route('booking.get-edit-booking', $appointment->id) }}">Edit Booking</a>
                                            @else
                                                <a class="grn-btn" href="{{ route('booking.get-booking', $appointment->id) }}">Book</a>
                                            @endif
                                            <div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center">
                        <br>
                        <p>Thank you for visiting our registration site. At this time, we are fully booked up.</p><br>
                        <p><b>We are open Friday (12/8) and Saturday (12/9) nights for Walk Ups.</b></p><br>
                        <p>We open at 6:30pm on Friday and 4pm on Saturday.</p><br>
                        <p>Please arrive as early as you can to get on the Walk Up list. You will be given a time when you check in at the desk.</p><br>
                        <p>We hope you are able join us. See ya soon!</p>
                    </div>
                    @endif
                </div>
            </div>
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>