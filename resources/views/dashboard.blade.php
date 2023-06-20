<x-app-layout>
<html>
    <head>
        <script src="{{asset('js/dist/jquery.min.js')}}"></script>
        <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
    </head>
    <body>

    <div class="container">
        <h1>Dashboard</h1>

        @if ($appointments->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Start Time</th>
                        <th>Slots Booked</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->title }}</td>
                            <td>{{ $appointment->description }}</td>
                            <td>{{ $appointment->start_time }}</td>
                            <td>{{ \App\Models\AppointmentUser::where('appointment_id', $appointment->id)->sum('slots_taken')}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No appointments found.</p>
        @endif
        
    </div>
    </body>
    </html>
</x-app-layout>
