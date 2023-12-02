<x-app-layout>
    <html>
        <head>
            <title>Appointments - WTB Registration</title>
            <link rel="stylesheet" href="{{ version('css/main.css') }}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                
                <div class="container" >
                    <form id="form" method="GET" action="{{ route('admin-booking.lookup') }}">
                        @csrf
                        @method('GET')
                        <div class="filter-container flex justify-center items-center h-screen mb-6 mt-6">
                            <div id="filter-inputs-container" class="form-container togglers">
                                <p class="mb-4 text-lg">Enter a user's name:</p>
                                <div class="form-group">
                                    <label for="name"></label>
                                    <div class="flex">
                                        <input type="text" name="name" id="name" value="{{ request('name') }}" class="form-control mr-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <script>
                        let input = $('#name')
                        input.focus()
                        input[0].setSelectionRange(input.val().length, input.val().length)
                        input.keyup(function () {
                            $('#form').submit()
                        })
                    </script>

                    <div class="tab-container appointment-table">
                        <table class="table mx-auto border border-slate-300 appt-pagination">  
                            <thead>
                                <tr class="border border-slate-300">
                                    <th class="border border-slate-300">Name</th>
                                    <th class="border border-slate-300">Email</th>
                                    <th class="border border-slate-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="border border-slate-300">
                                        <td class="border border-slate-300">{{ $user->getFirstName() }} {{ $user->getLastName() }}</td>
                                        <td class="border border-slate-300">{{ $user->getEmail() }}</td>
                                        <td class="border border-slate-300">
                                            @if ($user->hasUpcomingAppointment())
                                                <a class="grn-btn" href="{{ route('admin-booking.user', $user->getId()) }}">Edit Bookings</a>
                                            @else
                                                Nothing Upcoming
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>