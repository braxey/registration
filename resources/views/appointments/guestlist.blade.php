@php
    use App\Models\AppointmentUser;
    use App\Models\Appointment;
    use App\Models\User;
@endphp
<x-app-layout>
    <html>
        <head>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                <div>
                    <h1>Guestlist</h1>

                    <table class="border border-slate-300">
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
                            @foreach ($apptUsers as $apptUser)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ Appointment::findOrFail($apptUser->appointment_id)->title }}</td>
                                    <td class="border border-slate-300">{{ Appointment::findOrFail($apptUser->appointment_id)->start_time }}</td>
                                    <td class="border border-slate-300">{{ User::findOrFail($apptUser->user_id)->name }}</td>
                                    <td class="border border-slate-300">{{ $apptUser->slots_taken }}</td>
                                    @php $count += $apptUser->slots_taken; @endphp
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="3"><b>Total Bookings: </b></td> 
                                <td >{{$count}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>