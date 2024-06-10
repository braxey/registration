<x-app-layout>
    <html>
        <head>
            <title>{{ __('Dashboard - WTB Registration') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/UserDashboard.js')) }}"></script>
        </head>
        <body>
            <div id="user_dashboard_container" class="justify-center text-center">
                <div class="container">
                    <h1 class="flex justify-center" style="font-size: larger; margin-bottom: 25px">{{ $user->getFirstName() }}'s Dashboard</h1>

                    <div class="tab-container">
                        <div class="tabs">
                            <button class="tab active" data-tab="all" id="all_appointments_tab">{{ __('All Appointments') }}</button>
                            <button class="tab" data-tab="upcoming" id="upcoming_appointments_tab">{{ __('Upcoming Appointments') }}</button>
                            <button class="tab" data-tab="past" id="completed_appointments_tab">{{ __('Past Appointments') }}</button>
                        </div>
                        <div class="tab-content">
                            <div id="all_appointments_table" class="appointment-table">
                                <!-- All appointments table content here -->
                                @if ($allAppointments->count() > 0)
                                    <table class="table mx-auto border border-slate-300">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">{{ __('Start Time') }}</th>
                                                <th class="border border-slate-300">{{ __('Slots Booked') }}</th>
                                                <th class="border border-slate-300">{{ __('Status') }}</th>
                                                <th class="border border-slate-300">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($allAppointments as $appointment)
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ $appointment->userSlots($user->getId()) }}</td>
                                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->getStatus() }}</span></td>
                                                    <td class="border border-slate-300 flex justify-center items-center text-center">
                                                    @if($appointment->canEdit() && $organization->registrationIsOpen())
                                                        <div class="button-container">
                                                            <form action="{{ route('booking.get-edit-booking', $appointment->getId()) }}" method="GET" id="edit-form">
                                                                @csrf
                                                                @method('GET')
                                                                <button class="grn-btn" type="submit">{{ __('Edit Booking') }}</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <div>{{ __('N/A') }}</div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>{{ __('No past or upcoming appointments.') }}</p>
                                @endif
                            </div>
                            <div id="upcoming_appointments_table" class="appointment-table" style="display: none;">
                                <!-- Upcoming appointments table content here -->
                                @if ($upcomingAppointments->count() > 0)
                                    <!-- component -->
                                    <table class="table mx-auto border border-slate-300">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">{{ __('Start Time') }}</th>
                                                <th class="border border-slate-300">{{ __('Slots Booked') }}</th>
                                                <th class="border border-slate-300">{{ __('Status') }}</th>
                                                <th class="border border-slate-300">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($upcomingAppointments as $appointment)
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ $appointment->userSlots($user->getId()) }}</td>
                                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->getStatus() }}</span></td>
                                                    <td class="border border-slate-300 flex justify-center items-center text-center">
                                                    @if($appointment->canEdit() && $organization->registrationIsOpen())
                                                        <div class="button-container">
                                                            <form action="{{ route('booking.get-edit-booking', $appointment->getId()) }}" method="GET" id="edit-form">
                                                                @csrf
                                                                @method('GET')
                                                                <button class="grn-btn" type="submit">{{ __('Edit Booking') }}</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <div>{{ __('N/A') }}</div>
                                                    @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>{{ __('No upcoming appointments found.') }}</p>
                                @endif
                            </div>
                            <div id="completed_appointments_table" class="appointment-table" style="display: none;">
                                <!-- Past appointments table content here -->
                                @if ($pastAppointments->count() > 0)
                                    <table class="table mx-auto border border-slate-300">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">{{ __('Start Time') }}</th>
                                                <th class="border border-slate-300">{{ __('Slots Booked') }}</th>
                                                <th class="border border-slate-300">{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pastAppointments as $appointment)
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ $appointment->userSlots($user->getId()) }}</td>
                                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->getStatus() }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>{{ __('No past appointments found.') }}</p>
                                @endif
                            </div>
                        </div>
                        <div style="margin-bottom: 200px; text-align: left;">
                            <b>{{ __('Current number of slots booked:') }} {{ $user->getCurrentNumberOfSlots() }}/{{ $organization->getMaxSlotsPerUser() }}</b>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>
