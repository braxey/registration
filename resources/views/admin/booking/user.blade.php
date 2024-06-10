<x-app-layout>
    <html>
        <head>
            <title>{{ __('User Bookings - WTB Registration') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/AdminBooking.js')) }}"></script>
        </head>
        <body>
            <div class="justify-center">
                <h1 class="flex justify-center mb-6" style="font-size: larger">{{ $user->getName() }}'s {{ __('Upcoming Appointments') }}</h1>
                <div class="container" >
                    <div class="tab-container appointment-table">
                        <table class="table mx-auto border border-slate-300 appt-pagination">  
                            <thead>
                                <tr class="border border-slate-300">
                                    <th class="border border-slate-300">{{ __('Start Time') }}</th>
                                    <th class="border border-slate-300">{{ __('Slots') }}</th>
                                    <th class="border border-slate-300">{{ __('Status') }}</th>
                                    <th class="border border-slate-300">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($appointments as $appointment)
                                    <tr class="border border-slate-300">
                                        <td class="border border-slate-300">{{ $appointment->getParsedStartTime()->format('F d, Y g:i A') }}</td>
                                        <td class="border border-slate-300">{{ $appointment->userSlots($user->getId()) }}</td>
                                        <td class="border border-slate-300">
                                            <span class="highlight text-white">{{ $appointment->getStatus() }}</span>
                                        </td>
                                        <td class="border border-slate-300">
                                            <a class="grn-btn" href="{{ route('admin-booking.user-booking', ['userId' => $user->getId(), 'appointmentId' => $appointment->getId()]) }}">{{ __('Edit Booking') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>