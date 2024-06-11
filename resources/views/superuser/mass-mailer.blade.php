<x-app-layout>
    <html>
        <head>
            <title>{{ __('Create Mass Email') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
        </head>
        <body>
            <div class="flex justify-center text-center">
                <div class="container form-container">
                    <h1>{{ __('Create Mass Email') }}</h1>

                    <form action="{{ route('mass-mailer.send') }}" method="POST">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="recipients">{{ __('Select Recipient:') }}</label>
                            <select class="form-control" id="recipients" name="recipients">
                                <option value="all">{{ __('All Users') }}</option>
                                <option value="upcoming">{{ __('Users with Upcoming Appointments') }}</option>
                                <option value="completed">{{ __('Users with Completed Appointments') }}</option>
                            </select>
                        </div>

                        <div class="form-group form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="include-appointment-details" name="include-appointment-details">
                            <label class="form-check-label" for="include-appointment-details">{{ __('Include Appointment Details') }}</label>
                        </div>

                        <div class="form-group mb-4">
                            <label for="subject">{{ __('Subject:') }}</label>
                            <textarea class="form-control" id="subject" name="subject" rows="1"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="message">{{ __('Email Text:') }}</label>
                            <textarea class="form-control" id="message" name="message" rows="5"></textarea>
                        </div>

                        <button type="submit" class="grn-btn">{{ __('Send Email') }}</button>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>
