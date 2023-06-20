<x-app-layout>
    <html>
    <head>
        <script src="{{asset('js/dist/jquery.min.js')}}"></script>
        <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
    </head>
    <body>

    <div class="container">
        <h1>Edit Appointment</h1>

        <form action="{{ route('appointment.update', $appointment->id) }}" method="POST" id="update-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ $appointment->title }}">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ $appointment->description }}</textarea>
            </div>

            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="datetime-local" name="start_time" id="start_time" class="form-control" value="{{ $appointment->start_time }}">
            </div>

            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="datetime-local" name="end_time" id="end_time" class="form-control" value="{{ $appointment->end_time }}">
            </div>

            <div class="form-group">
                <label for="total_slots">Total Slots</label>
                <input type="text" name="total_slots" id="total_slots" class="form-control" value="{{ $appointment->total_slots }}">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
        <script type="module" src="{{ asset('js/appt/createedit.js') }}"></script>
    </div>
    <div>
    <form action="{{ route('appointment.delete', $appointment->id) }}" method="POST" id="delete-form">
        @csrf
        <button type="submit">Delete</button>
    </form>
    </div>
    </body>
    </html>
</x-app-layout>