<x-app-layout>
    <div>
        <h1>Appointments</h1>

        <table class="border border-slate-300">
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
                        <td class="border border-slate-300">{{ $appointment->total_slots - $appointment->slots_taken }}</td>
                        <td class="border border-slate-300">
                            @if ($user && $user->admin)
                            <a href="{{ route('appointment.edit', $appointment->id) }}">Edit</a>
                            @endif
                            <a href="{{ route('appointment.book', $appointment->id) }}">Book</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($user && $user->admin)
            <a href="{{ route('appointment.create_form') }}">Create</a>
        @endif
    </div>
</x-app-layout>