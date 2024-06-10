<x-app-layout>
    <html>
        <head>
            <title>{{ __('Edit Appointment') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/Appointment.js')) }}"></script>
        </head>
        <body>
            <div id="edit_appointment_container" class="flex justify-center text-center">
                <div class="container form-container">
                    <h1>{{ __('Edit Appointment') }}</h1>

                    <form action="{{ route('appointment.update', $appointment->getId()) }}" method="POST" id="edit_appointment_form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="description">{{ __('Description') }}</label>
                            <textarea name="description" id="description" class="form-control">{{ $appointment->getDescription() }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="start_time">{{ __('Start Time') }}</label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ $appointment->getStartTime() }}">
                        </div>

                        <div class="form-group">
                            <label for="total_slots">{{ __('Total Slots') }}</label>
                            <input type="text" name="total_slots" id="total_slots" class="form-control" value="{{ $appointment->getTotalSlots() }}">
                        </div>

                        <div class="form-group py-4">
                            <label for="walk-in-only">{{ __('Walk-in Only:') }}</label>
                            <input type="checkbox" name="walk-in-only" id="walk-in-only" {{ $appointment->isWalkInOnly() ? "checked=checked" : "" }}">
                        </div>

                        <button type="submit" class="grn-btn">{{ __('Update') }}</button>
                    </form>
                
                    <form action="{{ route('appointment.delete', $appointment->getId()) }}" method="POST" id="delete_appointment_form">
                        @csrf
                        <div class="form-group" style="text-align:left">
                            <label for="del">{{ __('Do you want to permanantly delete the appointment?') }}</label>
                            <button type="submit" class="red-btn">{{ __('Delete') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>