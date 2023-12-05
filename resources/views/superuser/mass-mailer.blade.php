<x-app-layout>
    <html>
        <head>
            <title>Create Mass Email</title>
            <script type="text/javascript" src="{{ version('js/dist/jquery.min.js') }}"></script>
            <script type="text/javascript" src="{{ version('js/dist/sweetalert2.all.min.js') }}"></script>
            <link rel="stylesheet" href="{{ version('css/main.css') }}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <h1>Create Mass Email</h1>

                    <form action="{{ route('mass-mailer.send') }}" method="POST" id="mass-email-form">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="recipients">Select Recipient:</label>
                            <select class="form-control" id="recipients" name="recipients">
                                <option value="all">All Users</option>
                                <option value="upcoming">Users with Upcoming Appointments</option>
                                <option value="completed">Users with Completed Appointments</option>
                            </select>
                        </div>

                        <div class="form-group form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="include-appointment-details" name="include-appointment-details">
                            <label class="form-check-label" for="include-appointment-details">Include Appointment Details</label>
                        </div>

                        <div class="form-group mb-4">
                            <label for="subject">Subject:</label>
                            <textarea class="form-control" id="subject" name="subject" rows="1"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="message">Email Text:</label>
                            <textarea class="form-control" id="message" name="message" rows="5"></textarea>
                        </div>

                        <button type="submit" class="grn-btn">Send Email</button>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>
