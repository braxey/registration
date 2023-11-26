<x-app-layout>
    <html>
        <head>
            <title>Walk-Ins</title>
            <link rel="stylesheet" href="{{version('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                <div class="container">
                    <h1 class="flex justify-center items-center h-screen" style="font-size: larger">Walk-In Waitlist</h1>

                    <a class="flex justify-left h-screen grn-btn text-center" style="max-width: 125px; margin-left: 5px;" href="{{ route('walk-in.get-create') }}">New Walk-In</a>

                    <div class="tab-container">
                        <div class="tabs">
                            <button class="tab" data-tab="all" id="all-tab">All Walk-Ins</button>
                            <button class="tab active" data-tab="unassigned" id="unassigned-tab">Unassigned Walk-Ins</button>
                            <button class="tab" data-tab="assigned" id="assigned-tab">Assigned Walk-Ins</button>
                        </div>
                        <div class="tab-content">
                            <div id="all-table" class="appointment-table" style="display: none;">
                                <!-- All walk-ins table content here -->
                                @if ($walkIns->count() > 0)
                                    @include('appointments.partials.walk-in-table', ['unassigned' => 0, 'assigned' => 0])
                                @else
                                    <p>No walk-ins.</p>
                                @endif
                            </div>
                            <div id="unassigned-table" class="appointment-table">
                                <!-- Unassigned walk-ins table content here -->
                                @if ($walkIns->where('appointment_id', null)->count() > 0)
                                    @include('appointments.partials.walk-in-table', ['unassigned' => 1, 'assigned' => 0])
                                @else
                                    <p>No unassigned walk-ins.</p>
                                @endif
                            </div>
                            <div id="assigned-table" class="appointment-table" style="display: none;">
                                <!-- Assigned walk-ins table content here -->
                                @if ($walkIns->where('appointment_id', '<>', null)->count() > 0)
                                    @include('appointments.partials.walk-in-table', ['unassigned' => 0, 'assigned' => 1])
                                @else
                                    <p>No assigned walk-ins.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript" src="{{ version('js/appt/highlight.js') }}"></script>
            <script type="text/javascript" src="{{ version('js/appt/waitlist.js') }}"></script>
        </body>
    </html>
</x-app-layout>