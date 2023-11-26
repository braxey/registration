@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use App\Models\AppointmentUser;
    $user = Auth::user();
@endphp
<x-app-layout>
    <html>
        <head>
            <title>Guestlist</title>
            <link rel="stylesheet" href="{{version('css/main.css')}}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
            <script src="{{version('js/dist/sweetalert2.all.min.js')}}"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                <div class="container">
                    <h1 class="flex justify-center items-center h-screen" style="font-size: larger">Admin Guestlist</h1>

                    <!-- Filter Form -->
                    <form id="filter-form" method="GET" action="{{ route('guestlist') }}">
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
                                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="form-control mr-2">
                                        <input type="time" name="start_time" id="start_time" value="{{ request('start_time') }}" class="form-control">
                                    </div>
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
                                    <button type="submit" class="grn-btn togglers" id="filter-apply-button" style="display: none;">Apply</button>
                                    <button type="button" class="red-btn togglers" id="filter-clear-button" style="display: none;">Clear</button>
                                    <button id="toggle-filter-button" class="red-btn">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="flex justify-end">
                        <p class="mr-1"><b>Showed Up / Registered:</b></p>
                        <p id="totalShowed" class="mr-1">{{ $totalShowedUp }}</p>
                        <p>/ {{$totalSlotsTaken}}</p>
                    </div>
                    <table class="table mx-auto border border-slate-300 appt-pagination">
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">Start Time</th>
                                <th class="border border-slate-300">Status</th>
                                <th class="border border-slate-300">Guest</th>
                                <th class="border border-slate-300 slot-col">Slots</th>
                                <th class="border border-slate-300 slot-col">Showed</th>
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
                                            <i class="fa fa-info-circle" style="width: 30px; cursor: pointer;" id="note-{{$guest->id}}"></i>
                                            <script>
                                                $(('#note-'+'{{ $guest->id }}')).on('click', function () {
                                                    Swal.fire({
                                                        title: 'Notes for {{ $guest->name }}',
                                                        html: '<b>{{ $guest->notes }}</b>',
                                                        icon: 'info',
                                                        confirmButtonText: 'Close',
                                                    });
                                                });
                                            </script>
                                        @endif
                                        {{ $guest->name }}
                                    </td>
                                    <td class="border border-slate-300">{{ $guest->slots }}</td>
                                    <td class="border border-slate-300">{{ $guest->showed_up }}</td>
                                    @else
                                    <td class="border border-slate-300">{{ $guest->first_name }} {{ $guest->last_name }}</td>
                                    <td class="border border-slate-300">{{ $guest->slots_taken }}</td>
                                    <td class="border border-slate-300">
                                        <input type="number" class="showed-up-input" data-guest-id="{{ $guest->id }}" 
                                            value="{{ $guest->showed_up }}" min="0" style="max-width: 80px;">
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <script>var csrfToken = "{{ csrf_token() }}";</script>
            <script type="module" src="{{ version('js/appt/guestlist.js') }}"></script>
            <script type="module" src="{{ version('js/appt/highlight.js') }}"></script>
        </body>
    </html>
</x-app-layout>