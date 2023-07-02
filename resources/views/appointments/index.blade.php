@php
    use App\Models\AppointmentUser;
@endphp
<x-app-layout>
    <div class="flex justify-center items-center h-screen">
        <div>
            <h1>Appointments</h1>

            <table class="table border border-slate-300">
                <style>
                    .table {
                        width: 120%;
                        border-collapse: collapse;
                    }
                    .table th,
                    .table td {
                        padding: 8px;
                        text-align: center;
                        border-bottom: 2px solid #ddd;
                    }
                    .table th {
                        background-color: #f2f2f2;
                    }
                    .table tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .table tr:hover {
                        background-color: #e9e9e9;
                    }
                </style>    
                <thead>
                    <tr class="border border-slate-300">
                        <th class="border border-slate-300">Title</th>
                        <th class="border border-slate-300">Start Time</th>
                        <th class="border border-slate-300">Slots Open</th>
                        <th class="border border-slate-300">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr class="border border-slate-300">
                            <td class="border border-slate-300">{{ $appointment->title }}</td>
                            <td class="border border-slate-300">{{ $appointment->start_time }}</td>
                            <td class="border border-slate-300">{{ max($appointment->total_slots - $appointment->slots_taken, 0) }}</td>
                            <td class="border border-slate-300">
                                @if ($user && $user->admin)
                                    <a href="{{ route('appointment.edit', $appointment->id) }}">Edit</a>
                                @endif
                                @if ($appointment->start_time < now())
                                    Closed  
                                @elseif ($user?->id && AppointmentUser::where('user_id', $user->id)->where('appointment_id', $appointment->id)->exists())
                                    <a href="{{ route('appointment.editbooking', $appointment->id) }}">Edit Booking</a>
                                @else
                                    <a href="{{ route('appointment.book', $appointment->id) }}">Book</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($user && $user->admin)
                <a href="{{ route('appointment.create_form') }}">Create</a>
            @endif
        </div>
    </div>
</x-app-layout>