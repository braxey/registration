<table class="table mx-auto border border-slate-300 appt-pagination">  
    <thead>
        <tr class="border border-slate-300">
            <th class="border border-slate-300">{{ __('Name') }}</th>
            <th class="border border-slate-300">{{ __('Email') }}</th>
            <th class="border border-slate-300">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr class="border border-slate-300">
                <td class="border border-slate-300">{{ $user->getFirstName() }} {{ $user->getLastName() }}</td>
                <td class="border border-slate-300">{{ $user->getEmail() }}</td>
                <td class="border border-slate-300">
                    @if ($user->hasUpcomingAppointment())
                        <a class="grn-btn" href="{{ route('admin-booking.user', $user->getId()) }}">{{ __('Edit Bookings') }}</a>
                    @else
                    {{ __('Nothing Upcoming') }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>