<!DOCTYPE html>
<html>
    <body style="color: black; height: 100%; width: 100%;">
        <p>Hello {{ $recipient->getFirstName() }},</p>

        <p>{!! $customMessage !!}</p>

        @if ($includeAppointments)
            <br />
            @if ($appointments->count() > 1)
            <p>Here are the details for your appointments:</p>
            @else
            <p>Here are the details for your appointment:</p>
            @endif

            @foreach($appointments as $appointment)
            <ul>
                <li>Date and time: {{ $appointment->getParsedStartTime()->format('F j, Y g:i A') }} EST</li>
                <li>Number of slots booked: {{ $appointment->userSlots($recipient->getId()) }}</li>
            </ul>
            <br />
            @endforeach
        @endif
    </body>
</html>
