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
                    @if ($user && $user->admin)
                        <a class="flex justify-left h-screen grn-btn text-center" style="max-width: 125px; margin-left: 5px;" href="{{ route('appointment.create_form') }}">Create Appt</a>
                    @endif
                    <div class="tab-container">
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
                                        <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->status }}</span></td>
                                        <td class="border border-slate-300">
                                            @if ($user && $user->admin)
                                                <a class="red-btn" href="{{ route('appointment.edit', $appointment->id) }}">Edit Appt</a>
                                            @endif
                                            @if ($appointment->start_time < now() || !$organization->registration_open || $appointment->slots_taken >= $appointment->total_slots)
                                                Closed  
                                            @elseif ($user?->id && AppointmentUser::where('user_id', $user->id)->where('appointment_id', $appointment->id)->exists())
                                                <a class="grn-btn" href="{{ route('appointment.editbooking', $appointment->id) }}">Edit Booking</a>
                                            @else
                                                <a class="grn-btn" href="{{ route('appointment.book', $appointment->id) }}">Book</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>