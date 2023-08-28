@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use App\Models\Appointment;
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

                    <a class="flex justify-left h-screen grn-btn text-center" style="max-width: 125px; margin-left: 5px;" href="{{ route('walk-in.create-form') }}">New Walk-In</a>

                    <div class="tab-container">
                        <div class="tabs">
                            <button class="tab" data-tab="all" id="all-tab">All Walk-Ins</button>
                            <button class="tab active" data-tab="unassigned" id="unassigned-tab">Unassigned Walk-Ins</button>
                            <button class="tab" data-tab="assigned" id="assigned-tab">Assigned Walk-Ins</button>
                        </div>
                        <div class="tab-content">
                            <div id="all-table" class="appointment-table" style="display: none;">
                                <!-- All appointments table content here -->
                                @if ($walkIns->count() > 0)
                                    <!-- component -->
                                    <table class="table mx-auto border border-slate-300 appt-pagination">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">Time Entered</th>
                                                <th class="border border-slate-300">Phone Number</th>
                                                <th class="border border-slate-300">Name</th>
                                                <th class="border border-slate-300 slot-col">Slots</th>
                                                <th class="border border-slate-300 slot-col">Desired Time</th>
                                                <th class="border border-slate-300 slot-col">Appointment</th>
                                                <th class="border border-slate-300 slot-col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($walkIns as $walkIn)
                                                @php
                                                    $desiredTime = $walkIn->desired_time < now() ? "Now" : \Carbon\Carbon::parse($walkIn->desired_time)->format('g:i A');
                                                    if ($walkIn->appointment_id === null) {
                                                        $linkedAppt = "Unassigned";
                                                    } else {
                                                        $appt = Appointment::find($walkIn->appointment_id);
                                                        $linkedAppt = \Carbon\Carbon::parse($appt->start_time)->format('g:i A');
                                                    }
                                                    $color = $linkedAppt === "Unassigned" ? "red" : "grn";
                                                @endphp
                                                <tr class="border border-slate-300">
                                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($walkIn->created_at)->format('F d, Y g:i A') }}</td>
                                                    <td class="border border-slate-300">{{ formatPhoneBrackets($walkIn->phone_number) }}</td>
                                                    <td class="border border-slate-300">{{ $walkIn->name }}</td>
                                                    <td class="border border-slate-300">{{ $walkIn->slots }}</td>
                                                    <td class="border border-slate-300">{{ $desiredTime }}</td>
                                                    <td class="border border-slate-300">
                                                        <a class="{{$color}}-btn text-center" href="{{ route('walk-in.link-appt', $walkIn->id) }}">{{$linkedAppt}}</a>
                                                    </td>
                                                    <td class="border border-slate-300">
                                                        <a class="red-btn text-center" href="{{ route('walk-in.edit-form', $walkIn->id) }}">Edit</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No walk-ins.</p>
                                @endif
                            </div>
                            <div id="unassigned-table" class="appointment-table">
                                <!-- Upcoming appointments table content here -->
                                @if ($walkIns->where('appointment_id', null)->count() > 0)
                                    <!-- component -->
                                    <table class="table mx-auto border border-slate-300 appt-pagination">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">Time Entered</th>
                                                <th class="border border-slate-300">Phone Number</th>
                                                <th class="border border-slate-300">Name</th>
                                                <th class="border border-slate-300 slot-col">Slots</th>
                                                <th class="border border-slate-300 slot-col">Desired Time</th>
                                                <th class="border border-slate-300 slot-col">Appointment</th>
                                                <th class="border border-slate-300 slot-col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($walkIns as $walkIn)
                                                @if ($walkIn->appointment_id == null)
                                                    @php
                                                        $desiredTime = $walkIn->desired_time < now() ? "Now" : \Carbon\Carbon::parse($walkIn->desired_time)->format('g:i A');
                                                        if ($walkIn->appointment_id === null) {
                                                            $linkedAppt = "Unassigned";
                                                        } else {
                                                            $appt = Appointment::find($walkIn->appointment_id);
                                                            $linkedAppt = \Carbon\Carbon::parse($appt->start_time)->format('g:i A');
                                                        }
                                                        $color = $linkedAppt === "Unassigned" ? "red" : "grn";
                                                    @endphp
                                                    <tr class="border border-slate-300">
                                                        <td class="border border-slate-300">{{ \Carbon\Carbon::parse($walkIn->created_at)->format('F d, Y g:i A') }}</td>
                                                        <td class="border border-slate-300">{{ formatPhoneBrackets($walkIn->phone_number) }}</td>
                                                        <td class="border border-slate-300">{{ $walkIn->name }}</td>
                                                        <td class="border border-slate-300">{{ $walkIn->slots }}</td>
                                                        <td class="border border-slate-300">{{ $desiredTime }}</td>
                                                        <td class="border border-slate-300">
                                                            <a class="{{$color}}-btn text-center" href="{{ route('walk-in.link-appt', $walkIn->id) }}">{{$linkedAppt}}</a>
                                                        </td>
                                                        <td class="border border-slate-300">
                                                            <a class="red-btn text-center" href="{{ route('walk-in.edit-form', $walkIn->id) }}">Edit</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No unassigned walk-ins.</p>
                                @endif
                            </div>
                            <div id="assigned-table" class="appointment-table" style="display: none;">
                                <!-- Past appointments table content here -->
                                @if ($walkIns->where('appointment_id', '<>', null)->count() > 0)
                                <table class="table mx-auto border border-slate-300 appt-pagination">
                                        <thead>
                                            <tr class="border border-slate-300">
                                                <th class="border border-slate-300">Time Entered</th>
                                                <th class="border border-slate-300">Phone Number</th>
                                                <th class="border border-slate-300">Name</th>
                                                <th class="border border-slate-300 slot-col">Slots</th>
                                                <th class="border border-slate-300 slot-col">Desired Time</th>
                                                <th class="border border-slate-300 slot-col">Appointment</th>
                                                <th class="border border-slate-300 slot-col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($walkIns as $walkIn)
                                                @if ($walkIn->appointment_id !== null)
                                                    @php
                                                        $desiredTime = $walkIn->desired_time < now() ? "Now" : \Carbon\Carbon::parse($walkIn->desired_time)->format('g:i A');
                                                        if ($walkIn->appointment_id === null) {
                                                            $linkedAppt = "Unassigned";
                                                        } else {
                                                            $appt = Appointment::find($walkIn->appointment_id);
                                                            $linkedAppt = \Carbon\Carbon::parse($appt->start_time)->format('g:i A');
                                                        }
                                                        $color = $linkedAppt === "Unassigned" ? "red" : "grn";
                                                    @endphp
                                                    <tr class="border border-slate-300">
                                                        <td class="border border-slate-300">{{ \Carbon\Carbon::parse($walkIn->created_at)->format('F d, Y g:i A') }}</td>
                                                        <td class="border border-slate-300">{{ formatPhoneBrackets($walkIn->phone_number) }}</td>
                                                        <td class="border border-slate-300">{{ $walkIn->name }}</td>
                                                        <td class="border border-slate-300">{{ $walkIn->slots }}</td>
                                                        <td class="border border-slate-300">{{ $desiredTime }}</td>
                                                        <td class="border border-slate-300">
                                                            <a class="{{$color}}-btn text-center" href="{{ route('walk-in.link-appt', $walkIn->id) }}">{{$linkedAppt}}</a>
                                                        </td>
                                                        <td class="border border-slate-300">
                                                            <a class="red-btn text-center" href="{{ route('walk-in.edit-form', $walkIn->id) }}">Edit</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p>No assigned walk-ins.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
            <script type="module" src="{{ asset('js/appt/waitlist.js') }}"></script>
        </body>
    </html>
</x-app-layout>