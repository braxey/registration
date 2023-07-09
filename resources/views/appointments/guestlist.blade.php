@php
    use Carbon\Carbon;
@endphp
<x-app-layout>
    <html>
        <head>
            <title>Guestlist</title>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                <div class="container">
                    <h1 class="flex justify-center items-center h-screen" style="font-size: larger">Admin Guestlist</h1>

                    <!-- Filter Form -->
                    <form id="filter-form" method="GET" action="{{ route('appointments.guestlist') }}">
                        @csrf
                        @method('GET')
                        <div class="filter-container flex justify-center items-center h-screen">
                            <div id="filter-inputs-container" style="display: none;" class="form-container togglers">
                                <div class="form-group flex">
                                    <div class = "mr-2">
                                        <label for="first_name">First Name:</label>
                                        <input type="text" name="first_name" id="first_name" value="{{ request('first_name') }}" class="form-control">
                                    </div>
                                    <div>
                                        <label for="last_name">Last Name:</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ request('last_name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="start_date_time">Start Date and Time</label>
                                    <div class="flex">
                                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control">
                                        <input type="time" name="start_time" id="start_time" value="{{ request('start_time') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="appointment_name">Appointment Name:</label>
                                    <input type="text" name="appointment_name" id="appointment_name" value="{{ request('appointment_name') }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="" {{ request('status') === '' ? 'selected' : '' }}>All</option>
                                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="in progress" {{ request('status') === 'in progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complete</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-container" style="border: none; max-width: 1000px !important;">
                                <div class="button-container" style="justify-content: left;" id="filter-buttons">
                                    <button type="submit" class="red-btn togglers" id="filter-apply-button" style="display: none;">Apply</button>
                                    <button type="button" class="red-btn togglers" id="filter-clear-button" style="display: none;">Clear</button>
                                    <button id="toggle-filter-button" class="red-btn">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div align="right">
                        <b>Total Slots in Table: </b> {{$totalSlotsTaken}}
                    </div>
                    <table class="table mx-auto border border-slate-300 appt-pagination">
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">Start Time</th>
                                <th class="border border-slate-300">Status</th>
                                <th class="border border-slate-300">Guest</th>
                                <th class="border border-slate-300 slot-col">Slots</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guests as $guest)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($guest->appointment->start_time)->format('F d, Y g:i A') }}</td>
                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $guest->appointment->status }}</span></td>
                                    <td class="border border-slate-300">{{ $guest->user->first_name }} {{ $guest->user->last_name }}</td>
                                    <td class="border border-slate-300">{{ $guest->slots_taken }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <script type="module" src="{{ asset('js/appt/guestlist.js') }}"></script>
            <script type="module" src="{{ asset('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>