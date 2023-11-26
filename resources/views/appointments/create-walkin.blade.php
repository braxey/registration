<x-app-layout>
    <html>
        <head>
            <title>Create Walk-In</title>
            <script src="{{asset('js/dist/jquery.min.js')}}"></script>
            <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <h1>Create Walk-In</h1>

                    <form action="{{ route('walk-in.create') }}" method="POST" id="create-form">
                        @csrf

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email (optional)</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="slots">Slots</label>
                            <input type="text" name="slots" id="slots" class="form-control" value="1" required>
                        </div>

                        <div class="form-group">
                            <label for="desired_time">Desired Time</label>
                            <?php $startTime = date('Y-m-d\TH:i', strtotime(now('EST'))); ?>
                            <input type="datetime-local" name="desired_time" id="desired_time" class="form-control" value="{{ $startTime }}">
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (optional)</label>
                            <input type="text" name="notes" id="notes" class="form-control">
                        </div>

                        <button type="submit" class="grn-btn">Create</button>
                    </form>
                    <script type="module" src="{{ asset('js/appt/createedit-walkin.js') }}"></script>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>