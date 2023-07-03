<x-app-layout>
    <html>
        <head>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
            <script src="{{asset('js/dist/jquery.min.js')}}"></script>
            <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container">
                    <h1>Dashboard</h1>

                    @if ($upcomingAppointments->count() > 0)
                        <h2>Upcoming Appointments</h2>
                        <!-- component -->
                        <table class="table mx-auto border border-slate-300">
                            <thead>
                                <tr class="border border-slate-300">
                                    <th class="border border-slate-300">Title</th>
                                    <th class="border border-slate-300">Description</th>
                                    <th class="border border-slate-300">Start Time</th>
                                    <th class="border border-slate-300">Slots Booked</th>
                                    <th class="border border-slate-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($upcomingAppointments as $appointment)
                                    <tr class="border border-slate-300">
                                        <td class="border border-slate-300">{{ $appointment->title }}</td>
                                        <td class="border border-slate-300">{{ $appointment->description }}</td>
                                        <td class="border border-slate-300">{{ $appointment->start_time }}</td>
                                        <td class="border border-slate-300">{{ \App\Models\AppointmentUser::where('appointment_id', $appointment->id)->sum('slots_taken') }}</td>
                                        @if($appointment->start_time > now())
                                        <td class="border border-slate-300 flex justify-center items-center h-screen text-center">
                                            <div class="button-container">
                                                <form action="{{ route('appointment.cancelbooking', $appointment->id) }}" method="POST" id="cancel-form">
                                                    @csrf
                                                    @method('POST')
                                                    <button class="red-btn" type="submit">Cancel</button>
                                                </form>
                                                <form action="{{ route('appointment.editbooking', $appointment->id) }}" method="GET" id="edit-form">
                                                    @csrf
                                                    @method('GET')
                                                    <button class="red-btn" type="submit">Edit</button>
                                                </form>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No upcoming appointments found.</p>
                    @endif

                    @if ($pastAppointments->count() > 0)
                        <h2>Past Appointments</h2>
                        <table class="table mx-auto border border-slate-300">
                            <thead>
                                <tr class="border border-slate-300">
                                    <th class="border border-slate-300">Title</th>
                                    <th class="border border-slate-300">Description</th>
                                    <th class="border border-slate-300">Start Time</th>
                                    <th class="border border-slate-300">Slots Booked</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pastAppointments as $appointment)
                                    <tr class="border border-slate-300">
                                        <td class="border border-slate-300">{{ $appointment->title }}</td>
                                        <td class="border border-slate-300">{{ $appointment->description }}</td>
                                        <td class="border border-slate-300">{{ $appointment->start_time }}</td>
                                        <td class="border border-slate-300">{{ \App\Models\AppointmentUser::where('appointment_id', $appointment->id)->sum('slots_taken') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No past appointments found.</p>
                    @endif
                    
                </div>
            </div>
        <script type="module" src="{{ asset('js/appt/cancelbooking.js') }}"></script>
        </body>
    </html>
</x-app-layout>
