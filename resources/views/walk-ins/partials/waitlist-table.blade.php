<table class="table mx-auto border border-slate-300 appt-pagination">
    <thead>
        <tr class="border border-slate-300">
            <th class="border border-slate-300">{{ __('Time Entered') }}</th>
            <th class="border border-slate-300">{{ __('Email') }}</th>
            <th class="border border-slate-300">{{ __('Name') }}</th>
            <th class="border border-slate-300 slot-col">{{ __('Slots') }}</th>
            <th class="border border-slate-300 slot-col">{{ __('Desired Time') }}</th>
            <th class="border border-slate-300 slot-col">{{ __('Appointment') }}</th>
            <th class="border border-slate-300 slot-col">{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($walkIns as $walkIn)
            @if (($unassigned && $walkIn->isAssigned()) || ($assigned && $walkIn->isNotAssigned()))
                @continue
            @endif

            @php
                $desiredTime = $walkIn->getParsedDesiredTime() < now('EST') ? "Now" : $walkIn->getParsedDesiredTime()->format('g:i A');
                if ($walkIn->isNotAssigned()) {
                    $linked = 'Unassigned';
                } else {
                    $linked = $walkIn->getAppointment()->getParsedStartTime()->format('g:i A');
                }
            @endphp
            <tr class="border border-slate-300">
                <td class="border border-slate-300">{{ $walkIn->getParsedCreatedAtTime()->format('F d, Y g:i A') }}</td>
                <td class="border border-slate-300" @if (!$walkIn->providedEmail()) style="color: red;" @endif>{{ $walkIn->getEmail() ?: "Not Provided" }}</td>
                <td class="border border-slate-300">{{ $walkIn->getName() }}</td>
                <td class="border border-slate-300">{{ $walkIn->getNumberOfSlots() }}</td>
                <td class="border border-slate-300">{{ $desiredTime }}</td>
                <td class="border border-slate-300">
                    <a class="{{ $walkIn->isAssigned() ? 'grn' : 'red' }}-btn text-center" href="{{ route('walk-in.get-link-appointment', $walkIn->getId()) }}">{{ $linked }}</a>
                </td>
                <td class="border border-slate-300">
                    <a class="red-btn text-center" href="{{ route('walk-in.get-edit', $walkIn->getId()) }}">{{ __('Edit') }}</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>