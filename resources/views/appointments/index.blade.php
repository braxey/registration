@php
    use App\Models\AppointmentUser;
@endphp
<x-app-layout>
    <html>
        <head>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                
                <div class="container">
                    <h1 class="mx-auto flex justify-center items-center h-screen">Appointments</h1>
                    @if ($user && $user->admin)
                        <a class="mx-auto flex justify-center items-center h-screen red-btn" style="max-width: 60px;" href="{{ route('appointment.create_form') }}">Create</a>
                    @endif
                    <table class="table mx-auto border border-slate-300">  
                        <thead>
                            <tr class="border border-slate-300">
                                <th class="border border-slate-300">Title</th>
                                <th class="border border-slate-300">Start Time</th>
                                <th class="border border-slate-300">Slots Open</th>
                                <th class="border border-slate-300">Status</th>
                                <th class="border border-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr class="border border-slate-300">
                                    <td class="border border-slate-300">{{ $appointment->title }}</td>
                                    <td class="border border-slate-300">{{ $appointment->start_time }}</td>
                                    <td class="border border-slate-300">{{ max($appointment->total_slots - $appointment->slots_taken, 0) }}</td>
                                    <td class="border border-slate-300">{{ $appointment->status }}</td>
                                    <td class="border border-slate-300">
                                        @if ($user && $user->admin)
                                            <a class="red-btn" href="{{ route('appointment.edit', $appointment->id) }}">Edit</a>
                                        @endif
                                        @if ($appointment->start_time < now())
                                            Closed  
                                        @elseif ($user?->id && AppointmentUser::where('user_id', $user->id)->where('appointment_id', $appointment->id)->exists())
                                            <a class="red-btn" href="{{ route('appointment.editbooking', $appointment->id) }}">Edit Booking</a>
                                        @else
                                            <a class="red-btn" href="{{ route('appointment.book', $appointment->id) }}">Book</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>