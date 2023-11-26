<!DOCTYPE html>
<html>
<body>
    <p>Hello {{ $name }},</p>
    @if ($update)
    <p>You have a Walk Thru Bethlehem booking has been successfully updated to the following details:</p>
    @else
    <p>You have a Walk Thru Bethlehem appointment for the following details:</p>
    @endif
    <ul>
        <li>Date and time: {{ $dateTime }} EST</li>
        <li>Number of slots booked: {{ $slots }}</li>
    </ul>
    <p>We hope you enjoy Walk Thru Bethlehem!</p>
</body>
</html>
