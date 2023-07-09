<x-app-layout>
<html>
    <head>
        <title>Book</title>
        <script src="{{asset('js/dist/jquery.min.js')}}"></script>
        <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
        <link rel="stylesheet" href="{{asset('css/main.css')}}">
    </head>
    <body>
        <div class="flex justify-center items-center h-screen text-center">
            <div class="container form-container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h2>Book Slots</h2>

                        <form action="{{ route('appointment.book', $appointment) }}" method="POST" id="book-form">
                            @csrf

                            <div class="form-group">
                                <label for="slots">Slots</label>
                                <input type="text" name="slots" id="slots" class="form-control">
                            </div>
                            <button class="grn-btn" type="submit">Book</button>
                        </form>
                        <script>
                            var slotsLeft = {{$availableSlots}}
                            var userSlots = {{$userSlots}}
                            var startTime = new Date("{{$appointment->start_time}}")
                        </script>
                        <script type="module" src="{{ asset('js/appt/book.js') }}"></script>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
</x-app-layout>
