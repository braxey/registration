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

                    <a class="flex justify-left h-screen grn-btn text-center" style="max-width: 125px;" href="{{ route('walk-in.create-form') }}">New Walk-In</a>

                    <table class="table mx-auto border border-slate-300 appt-pagination">
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">Time Entered</th>
                                <th class="border border-slate-300">Phone Number</th>
                                <th class="border border-slate-300">Name</th>
                                <th class="border border-slate-300 slot-col">Slots</th>
                                <th class="border border-slate-300 slot-col">Desired Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($walkIns as $walkIn)
                                @php
                                    $desiredTime = $walkIn->desired_time < now() ? "Now" : \Carbon\Carbon::parse($walkIn->desired_time)->format('g:i A');
                                @endphp
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($walkIn->created_at)->format('g:i A') }}</td>
                                    <td class="border border-slate-300">{{ $walkIn->phone_number }}</td>
                                    <td class="border border-slate-300">{{ $walkIn->name }}</td>
                                    <td class="border border-slate-300">{{ $walkIn->slots }}</td>
                                    <td class="border border-slate-300">{{ $desiredTime }}</td>
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