<x-app-layout>
    <html>
        <head>
            <title>{{ __('Walk-Ins') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/WalkIn.js')) }}"></script>
        </head>
        <body>
            <div id="walk_ins_waitlist_container" class="flex justify-center">
                <div class="container">
                    <h1 class="flex justify-center" style="font-size: larger">{{ __('Walk-In Waitlist') }}</h1>

                    <a class="flex justify-left grn-btn text-center" style="max-width: 125px; margin-left: 5px;" href="{{ route('walk-in.get-create') }}">New Walk-In</a>

                    <div class="tab-container">
                        <div class="tabs">
                            <button class="tab" data-tab="all" id="all_walk_ins_tab">{{ __('All Walk-Ins') }}</button>
                            <button class="tab active" data-tab="unassigned" id="unassigned_walk_ins_tab">{{ __('Unassigned Walk-Ins') }}</button>
                            <button class="tab" data-tab="assigned" id="assigned_walk_ins_tab">{{ __('Assigned Walk-Ins') }}</button>
                        </div>
                        <div class="tab-content">
                            <div id="all_walk_ins_table" class="appointment-table" style="display: none;">
                                <!-- All walk-ins table content here -->
                                @if ($walkIns->count() > 0)
                                    @include('walk-ins.partials.waitlist-table', ['unassigned' => 0, 'assigned' => 0])
                                @else
                                    <p>{{ __('No walk-ins.') }}</p>
                                @endif
                            </div>
                            <div id="unassigned_walk_ins_table" class="appointment-table">
                                <!-- Unassigned walk-ins table content here -->
                                @if ($walkIns->where('appointment_id', null)->count() > 0)
                                    @include('walk-ins.partials.waitlist-table', ['unassigned' => 1, 'assigned' => 0])
                                @else
                                    <p>{{ __('No unassigned walk-ins.') }}</p>
                                @endif
                            </div>
                            <div id="assigned_walk_ins_table" class="appointment-table" style="display: none;">
                                <!-- Assigned walk-ins table content here -->
                                @if ($walkIns->where('appointment_id', '<>', null)->count() > 0)
                                    @include('walk-ins.partials.waitlist-table', ['unassigned' => 0, 'assigned' => 1])
                                @else
                                    <p>{{ __('No assigned walk-ins.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>