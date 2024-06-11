<x-app-layout>
    <html>
        <head>
            <title>{{ __('Link Walk-In to Appointment') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/Appointment.js')) }}"></script>
        </head>
        <body>
            <div id="appointments_linking_container" class="flex justify-center">
                <div class="container" >
                    <!-- Filter Form -->
                    <form id="filter-form" method="GET" action="{{ route('walk-in.get-link-appointment', ['walkInId' => $walkIn->getId()]) }}">
                        @csrf
                        @method('GET')
                        <div class="filter-container flex justify-center mb-6 mt-6">
                            <div id="filter-inputs-container" class="form-container togglers">
                                <p class="mb-4 text-lg">{{ __('Select your preferred time range:') }}</p>
                                <div class="form-group">
                                    <label for="start_date_time">{{ __('Start Date and Time') }}</label>
                                    <div class="flex">
                                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control mr-2">
                                        <input type="time" name="start_time" id="start_time" value="{{ request('start_time') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="end_date_time">{{ __('End Date and Time') }}</label>
                                    <div class="flex">
                                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control mr-2">
                                        <input type="time" name="end_time" id="end_time" value="{{ request('end_time') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="button-container" style="justify-content: center;" id="filter-buttons">
                                    <button id="linking_filter_apply_button" type="submit" class="grn-btn togglers">{{ __('Apply') }}</button>
                                    <button id="linking_filter_reset_button" type="button" class="red-btn togglers">{{ __('Reset') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>


                    <h1 class="flex justify-center" style="font-size: larger">{{ __('Appointments') }}</h1>
                    <table class="table mx-auto border border-slate-300 appt-pagination">  
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">{{ __('Start Time') }}</th>
                                <th class="border border-slate-300">{{ __('Slots Filled') }}</th>
                                <th class="border border-slate-300">{{ __('Showed Up') }}</th>
                                <th class="border border-slate-300">{{ __('Status') }}</th>
                                <th class="border border-slate-300">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nonCompletedAppointments as $appointment)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</td>
                                    <td class="border border-slate-300">{{ $appointment->getSlotsTaken() }} / {{ $appointment->getTotalSlots() }}</td>
                                    <td class="border border-slate-300">{{ $appointment->getShowedUp() }}</td>
                                    <td class="border border-slate-300">
                                        <span class="highlight text-white">{{ $appointment->getStatus() }}</span>
                                        {{ $appointment->isWalkInOnly() ? "W-I" : ""}}
                                    </td>
                                    <td class="border border-slate-300">
                                    @if ($appointment->getId() == $walkIn->getAppointmentId())
                                    <form action="{{ route('walk-in.unlink-appointment', ['walkInId' => $walkIn->getId(), 'appointmentId' => $appointment->getId()]) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <button type="submit" class="red-btn flex" style="margin: auto !important">{{ __('Unlink') }}</button>
                                        </div>
                                    </form>
                                    @else
                                    <form action="{{ route('walk-in.link-appointment', ['walkInId' => $walkIn->getId(), 'appointmentId' => $appointment->getId()]) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <button type="submit" class="grn-btn flex" style="margin: auto !important">{{ __('Link') }}</button>
                                        </div>
                                    </form>
                                    @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>