@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use App\Models\AppointmentUser;
@endphp
<x-app-layout>
    <html>
        <head>
            <title>Walk-Ins</title>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                <div class="container">
                    <h1 class="flex justify-center items-center h-screen" style="font-size: larger">Walk-In Waitlist</h1>

                    <table class="table mx-auto border border-slate-300 appt-pagination">
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">Time Entered</th>
                                <th class="border border-slate-300">Phone Number</th>
                                <th class="border border-slate-300">Name</th>
                                <th class="border border-slate-300 slot-col">Slots</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guests as $guest)
                                @php $apptUser = AppointmentUser::where('appointment_id', $guest->appointment_id)
                                                                                        ->where('user_id', $guest->user_id)
                                                                                        ->first();
                                @endphp
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($guest->appointment->start_time)->format('F d, Y g:i A') }}</td>
                                    <td class="border border-slate-300">{{ $guest->user->phone_number }}</td>
                                    <td class="border border-slate-300">{{ $guest->user->first_name }} {{ $guest->user->last_name }}</td>
                                    <td class="border border-slate-300">{{ $apptUser->slots_taken }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <script>var csrfToken = "{{ csrf_token() }}";</script>
            <script type="module" src="{{ asset('js/appt/guestlist.js') }}"></script>
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>