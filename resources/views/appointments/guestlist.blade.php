@php
    use App\Models\AppointmentUser;
    use App\Models\Appointment;
    use App\Models\User;
@endphp
<x-app-layout>
    <html>
        <head>
            <script src="{{asset('js/dist/jquery.min.js')}}"></script>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                <div class="container">
                    <h1 class="flex justify-center items-center h-screen">Guestlist</h1>

                    <!-- Filter Form -->
                    <form id="filter-form" method="GET" action="{{ route('appointments.guestlist') }}">
                        <div class="filter-container flex justify-center items-center h-screen">
                            <button id="toggle-filter-button" class="btn btn-link filter-button">Filter</button>
                            <div id="filter-inputs-container" style="display: none;">
                                <div class="form-group">
                                    <label for="guest_name">Guest Name:</label>
                                    <input type="text" name="guest_name" id="guest_name" value="{{ request('guest_name') }}">
                                </div>
                                <div class="form-group">
                                    <label for="start_time">Start Time:</label>
                                    <input type="text" name="start_time" id="start_time" value="{{ request('start_time') }}">
                                </div>
                                <div class="form-group">
                                    <label for="appointment_name">Appointment Name:</label>
                                    <input type="text" name="appointment_name" id="appointment_name" value="{{ request('appointment_name') }}">
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="filter-apply-button">Apply</button>
                                    <button type="button" class="btn btn-secondary" id="filter-clear-button">Clear</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table class="table mx-auto border border-slate-300">
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">Title</th>
                                <th class="border border-slate-300">Start Time</th>
                                <th class="border border-slate-300">Guest</th>
                                <th class="border border-slate-300">Slots</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 0; @endphp
                            @foreach ($guests as $guest)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ $guest->appointment->title }}</td>
                                    <td class="border border-slate-300">{{ $guest->appointment->start_time }}</td>
                                    <td class="border border-slate-300">{{ $guest->user->name }}</td>
                                    <td class="border border-slate-300">{{ $guest->slots_taken }}</td>
                                </tr>
                                @php $count += $guest->slots_taken; @endphp
                            @endforeach
                            <tr class="border border-slate-300">
                                <td colspan="3" class="border border-slate-300" style="text-align: left;"><b>Total Bookings: </b></td> 
                                <td class="border border-slate-300">{{$count}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <script type="module" src="{{ asset('js/appt/guestlist.js') }}"></script>
        </body>
    </html>
</x-app-layout>