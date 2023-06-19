<x-app-layout>
    <div>
        <h1>Appointments</h1>

        <table class="table-auto">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Start Time</th>
                    <th>Slots Open</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->title }}</td>
                        <td>{{ $appointment->start_time }}</td>
                        <td>{{ $appointment->total_slots - $appointment->slots_taken }}</td>
                        <td>
                            @if ($user && $user->admin)
                            <a href="{{ route('appointment.edit', $appointment->id) }}">Edit</a>
                            @endif
                            <a href="{{ route('appointment.book', $appointment->id) }}">Book</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>