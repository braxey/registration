<x-app-layout>
    <html>
        <head>
            <title>{{ __('Guestlist') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/Appointment.js')) }}"></script>
        </head>
        <body>
            <div id="guestlist_container" class="flex justify-center">
                <div class="container">
                    <h1 class="flex justify-center" style="font-size: larger">{{ __('Admin Guestlist') }}</h1>

                    <!-- Filter Form -->
                    <form id="guestlist_filter_form" method="GET" action="{{ route('guestlist') }}">
                        @csrf
                        @method('GET')
                        <div class="filter-container flex justify-center">
                            <div id="filter-inputs-container" style="display: none;" class="form-container togglers">
                                <div class="form-group flex">
                                    <div class = "mr-2">
                                        <label for="first_name">{{ __('First Name:') }}</label>
                                        <input type="text" name="first_name" id="first_name" value="{{ request('first_name') }}" class="form-control">
                                    </div>
                                    <div>
                                        <label for="last_name">{{ __('Last Name:') }}</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ request('last_name') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="start_date_time">{{ __('Start Date and Time') }}</label>
                                    <div class="flex">
                                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control mr-2">
                                        <input type="time" name="start_time" id="start_time" value="{{ request('start_time') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="status">{{ __('Status:') }}</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="" {{ request('status') === '' ? 'selected' : '' }}>{{ __('All') }}</option>
                                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>{{ __('Upcoming') }}</option>
                                        <option value="in progress" {{ request('status') === 'in progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Complete') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-container" style="border: none; max-width: 1000px !important;">
                                <div class="button-container" style="justify-content: left;" id="guestlist_filter_buttons_container">
                                    <button type="submit" class="grn-btn togglers" id="guestlist_apply_filter_button" style="display: none;">{{ __('Apply') }}</button>
                                    <button type="button" class="red-btn togglers" id="guestlist_clear_filter_button" style="display: none;">{{ __('Clear') }}</button>
                                    <button id="guestlist_toggle_filter_button" class="red-btn">{{ __('Filter') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="flex justify-end">
                        <p class="mr-1"><b>{{ __('Showed Up / Registered:') }}</b></p>
                        <p id="guestlist_total_attendance" class="mr-1">{{ $totalShowedUp }}</p>
                        <p>/ {{ $totalSlotsTaken }}</p>
                    </div>
                    <table class="table mx-auto border border-slate-300 appt-pagination">
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">{{ __('Start Time') }}</th>
                                <th class="border border-slate-300">{{ __('Status') }}</th>
                                <th class="border border-slate-300">{{ __('Guest') }}</th>
                                <th class="border border-slate-300 slot-col">{{ __('Slots') }}</th>
                                <th class="border border-slate-300 slot-col">{{ __('Showed') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guests as $guest)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ \Carbon\Carbon::parse($guest->start_time)->format('F d, Y g:i A') }}</td>
                                    <td class="border border-slate-300"><span class="highlight text-white">{{ $guest->status }}</span></td>
                                    @if ($guest->is_walk_in)
                                    <td class="border border-slate-300 flex justify-center">
                                        @if ($guest->notes)
                                            <i class="fa fa-info-circle walk_in_note" style="width: 30px; cursor: pointer;"
                                                data-name="{{ $guest->name }}" data-notes="{{ $guest->notes }}"></i>
                                        @endif
                                        {{ $guest->name }}
                                    </td>
                                    <td class="border border-slate-300">{{ $guest->slots }}</td>
                                    <td class="border border-slate-300">{{ $guest->showed_up }}</td>
                                    @else
                                    <td class="border border-slate-300">{{ $guest->first_name }} {{ $guest->last_name }}</td>
                                    <td class="border border-slate-300">{{ $guest->slots_taken }}</td>
                                    <td class="border border-slate-300">
                                        <input type="number" class="guestlist_attendance_input" data-guest-id="{{ $guest->id }}" 
                                            value="{{ $guest->showed_up }}" min="0" style="max-width: 80px;">
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>