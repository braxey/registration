@php
    use Carbon\Carbon;
    use App\Models\WalkIn;
@endphp
<x-app-layout>
    <html>
        <head>
            <title>Appointments</title>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                
                <div class="container" >
                <h1 class="flex justify-center items-center h-screen" style="font-size: larger">Appointments</h1>
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
                            @php
                                $walkIn = WalkIn::find($id);
                            @endphp
                            @foreach ($nonCompletedAppointments as $appointment)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($appointment->start_time)->format('F d, Y g:i A') }}</td>
                                    <td class="border border-slate-300">{{ $appointment->slots_taken }} / {{ $appointment->total_slots }}</td>
                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $appointment->status }}</span></td>
                                    <td class="border border-slate-300">
                                    <form action="{{ route('walk-in.link-appt-post', ['walkInId' => $id, 'apptId' => $appointment->id]) }}" method="POST" id="link-form">
                                        @csrf
                                        <div class="form-group">
                                            @if ($appointment->id == $walkIn->appointment_id)
                                                <p class="justify-center flex">Linked</p>
                                            @else
                                                <button type="submit" class="grn-btn flex" style="margin: auto !important">Link</button>
                                            @endif
                                        </div>
                                    </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>