<x-app-layout>
    <html>
        <head>
            <title>Appointments - WTB Registration</title>
            <link rel="stylesheet" href="{{version('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="justify-center items-center h-screen">
                <h1 class="flex justify-center items-center h-screen mb-6" style="font-size: larger">{{ $user->getName() }}'s Upcoming Appointments</h1>
                <div class="container" >
                    <div class="tab-container appointment-table">
                        <table class="table mx-auto border border-slate-300 appt-pagination">  
                            <thead>
                                <tr class="border border-slate-300">
                                    <th class="border border-slate-300">Start Time</th>
                                    <th class="border border-slate-300">Slots</th>
                                    <th class="border border-slate-300">Status</th>
                                    <th class="border border-slate-300">Actions</th>
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
                                            <a class="grn-btn" href="{{ route('admin-booking.user-booking', ['userId' => $user->getId(), 'appointmentId' => $appointment->getId()]) }}">Edit Booking</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </body>
        <script type="text/javascript" src="{{ version('js/appt/highlight.js') }}"></script>
    </html>
</x-app-layout>