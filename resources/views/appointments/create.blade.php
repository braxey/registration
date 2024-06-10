<x-app-layout>
    <html>
        <head>
            <title>{{ __('Create Appointment') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/Appointment.js')) }}"></script>
        </head>
        <body>
            <div id="create_appointment_container" class="flex justify-center text-center">
                <div class="container form-container">
                    <h1>{{ __('Create Appointment') }}</h1>

                    <form action="{{ route('appointment.create') }}" method="POST" id="create_appointment_form">
                        @csrf

                        <div class="form-group">
                            <label for="description">{{ __('Description') }}</label>
                            <textarea name="description" id="description" class="form-control">{{ __('Description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="start_time">{{ __('Start Time') }}</label>
                            <?php $startTime = date('Y-m-d\TH:i', strtotime(now('EST'))); ?>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ $startTime }}">
                        </div>

                        <div class="form-group">
                            <label for="total_slots">{{ __('Total Slots') }}</label>
                            <input type="text" name="total_slots" id="total_slots" class="form-control" value="{{ 15 }}">
                        </div>

                        <div class="form-group py-4">
                            <label for="walk-in-only">{{ __('Walk-in Only:') }}</label>
                            <input type="checkbox" name="walk-in-only" id="walk-in-only">
                        </div>

                        <button type="submit" class="grn-btn">{{ __('Create') }}</button>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>