@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use App\Models\AppointmentUser;
    $user = Auth::user();
@endphp
<x-app-layout>
    <html>
        <head>
            <title>Dashboard</title>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
            <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container">
                    <h1 class="flex justify-center items-center h-screen" style="font-size: larger; margin-bottom: 25px">{{$user->first_name}}'s Dashboard</h1>

                    <div class="tab-container">
                        <div class="tabs">
                            <button class="tab active" data-tab="all" id="all-tab">All Appointments</button>
                            <button class="tab" data-tab="upcoming" id="upcoming-tab">Upcoming Appointments</button>
                            <button class="tab" data-tab="past" id="past-tab">Past Appointments</button>
                        </div>
                        <div class="tab-content">
                            <div id="all-table" class="appointment-table">
                                <!-- All appointments table content here -->
                                @if ($allAppointments->count() > 0)
                                    <!-- component -->
                                    <table class="table mx-auto border border-slate-300">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">Start Time</th>
                                                <th class="border border-slate-300">Slots Booked</th>
                                                <th class="border border-slate-300">Status</th>
                                                <th class="border border-slate-300">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($allAppointments as $appointment)
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ AppointmentUser::where('appointment_id', $appointment->id)->where('user_id', $user->id)->sum('slots_taken') }}</td>
                                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->status }}</span></td>
                                                    <td class="border border-slate-300 flex justify-center items-center h-screen text-center">
                                                    @if($appointment->start_time > now() && $organization->registration_open)
                                                        <div class="button-container">
                                                            <form action="{{ route('appointment.editbooking', $appointment->id) }}" method="GET" id="edit-form">
                                                                @csrf
                                                                @method('GET')
                                                                <button class="grn-btn" type="submit">Edit Booking</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <div>N/A</div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No past or upcoming appointments.</p>
                                @endif
                            </div>
                            <div id="upcoming-table" class="appointment-table" style="display: none;">
                                <!-- Upcoming appointments table content here -->
                                @php $currCount = 0; @endphp
                                @if ($upcomingAppointments->count() > 0)
                                    <!-- component -->
                                    <table class="table mx-auto border border-slate-300">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">Start Time</th>
                                                <th class="border border-slate-300">Slots Booked</th>
                                                <th class="border border-slate-300">Status</th>
                                                <th class="border border-slate-300">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($upcomingAppointments as $appointment)
                                                @php 
                                                    $currCount += (\App\Models\AppointmentUser::where('appointment_id', $appointment->id)
                                                                                            ->where('user_id', $user->id)
                                                                                            ->sum('slots_taken')); 
                                                @endphp
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ \App\Models\AppointmentUser::where('appointment_id', $appointment->id)->where('user_id', $user->id)->sum('slots_taken') }}</td>
                                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->status }}</span></td>
                                                    <td class="border border-slate-300 flex justify-center items-center h-screen text-center">
                                                    @if($appointment->start_time > now() && $organization->registration_open)
                                                        <div class="button-container">
                                                            <form action="{{ route('appointment.editbooking', $appointment->id) }}" method="GET" id="edit-form">
                                                                @csrf
                                                                @method('GET')
                                                                <button class="grn-btn" type="submit">Edit Booking</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <div>N/A</div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No upcoming appointments found.</p>
                                @endif
                            </div>
                            <div id="past-table" class="appointment-table" style="display: none;">
                                <!-- Past appointments table content here -->
                                @if ($pastAppointments->count() > 0)
                                    <table class="table mx-auto border border-slate-300">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">Start Time</th>
                                                <th class="border border-slate-300">Slots Booked</th>
                                                <th class="border border-slate-300">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pastAppointments as $appointment)
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ \App\Models\AppointmentUser::where('appointment_id', $appointment->id)->where('user_id', $user->id)->sum('slots_taken') }}</td>
                                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->status }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No past appointments found.</p>
                                @endif
                            </div>
                        </div>
                        <div align="right" style="margin-bottom: 200px;">
                            <b>Current number of slots booked: {{$currCount}}/{{$organization->max_slots_per_user}}</b>
                        </div>
                    </div>
                </div>
            </div>
        <script type="module" src="{{ asset('js/appt/cancelbooking.js') }}"></script>
        <script type="module" src="{{ asset('js/appt/dashboard.js') }}"></script>
        <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>
