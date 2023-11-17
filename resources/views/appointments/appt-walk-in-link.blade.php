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

                <!-- Filter Form -->
                <form id="filter-form" method="GET" action="{{ route('walk-in.link-appt', ['id' => request('id')]) }}">
                    @csrf
                    @method('GET')
                    <div class="filter-container flex justify-center items-center h-screen mb-6 mt-6">
                        <div id="filter-inputs-container" class="form-container togglers">
                            <p class="mb-4 text-lg">Select your preferred time range:</p>
                            <div class="form-group">
                                <label for="start_date_time">Start Date and Time</label>
                                <div class="flex">
                                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control mr-2">
                                    <input type="time" name="start_time" id="start_time" value="{{ request('start_time') }}" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="end_date_time">End Date and Time</label>
                                <div class="flex">
                                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="form-control mr-2">
                                    <input type="time" name="end_time" id="end_time" value="{{ request('end_time') }}" class="form-control">
                                </div>
                            </div>
                            <div class="button-container" style="justify-content: center;" id="filter-buttons">
                                <button type="submit" class="grn-btn togglers" id="apply-filter">Apply</button>
                                <button type="button" class="red-btn togglers" onclick="$('#start_date').val('');$('#start_time').val('');$('#end_date').val('');$('#end_time').val('');$('#apply-filter').click();">Reset</button>
                            </div>
                        </div>
                    </div>
                </form>


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
                                    <td class="border border-slate-300">
                                        <span class="highlight text-white">{{ $appointment->status }}</span>
                                        {{ $appointment->isWalkInOnly() ? "W-I" : ""}}
                                    </td>
                                    <td class="border border-slate-300">
                                    @if ($appointment->id == $walkIn->appointment_id)
                                    <form action="{{ route('walk-in.unlink-appt-post', ['walkInId' => $id, 'apptId' => $appointment->id]) }}" method="POST" id="link-form">
                                        @csrf
                                        <div class="form-group">
                                            <button type="submit" class="red-btn flex" style="margin: auto !important">Unlink</button>
                                        </div>
                                    </form>
                                    @else
                                    <form action="{{ route('walk-in.link-appt-post', ['walkInId' => $id, 'apptId' => $appointment->id]) }}" method="POST" id="link-form">
                                        @csrf
                                        <div class="form-group">
                                            <button type="submit" class="grn-btn flex" style="margin: auto !important">Link</button>
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
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>