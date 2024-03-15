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