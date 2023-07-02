<x-app-layout>
    <html>
        <head>
            <script src="{{asset('js/dist/jquery.min.js')}}"></script>
            <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container">
                    <h1>Create Appointment</h1>

                    <form action="{{ route('appointment.create') }}" method="POST" id="create-form">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control">Description</textarea>
                        </div>

                        <div class="form-group">
                            <label for="start_time">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ now() }}">
                        </div>

                        <div class="form-group">
                            <label for="end_time">End Time</label>
                            <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ now()->addHours(1) }}">
                        </div>

                        <div class="form-group">
                            <label for="total_slots">Total Slots</label>
                            <input type="text" name="total_slots" id="total_slots" class="form-control" value="{{ 1 }}">
                        </div>

                        <button type="submit" class="red-btn">Create</button>
                    </form>
                    <script type="module" src="{{ asset('js/appt/createedit.js') }}"></script>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>