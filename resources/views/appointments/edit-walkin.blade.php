<x-app-layout>
    <html>
        <head>
            <title>Edit Walk-In</title>
            <script src="{{asset('js/dist/jquery.min.js')}}"></script>
            <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <h1>Edit Walk-In</h1>

                    <form action="{{ route('walk-in.edit', $walkIn->id) }}" method="POST" id="update-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $walkIn->name }}">
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ $walkIn->phone_number }}">
                        </div>

                        <div class="form-group">
                            <label for="slots">Slots</label>
                            <input type="text" name="slots" id="slots" class="form-control" value="{{ $walkIn->slots }}">
                        </div>

                        <div class="form-group">
                            <label for="desired_time">Desired Time</label>
                            <input type="datetime-local" name="desired_time" id="desired_time" class="form-control" value="{{ $walkIn->desired_time }}">
                        </div>

                        <button type="submit" class="grn-btn">Update</button>
                    </form>
                    <script type="module" src="{{ asset('js/appt/createedit-walkin.js') }}"></script>
                
                    <form action="{{ route('walk-in.delete', $walkIn->id) }}" method="POST" id="delete-form">
                        @csrf
                        <div class="form-group" style="text-align:left">
                            <label for="del">Do you want to permanantly delete the walk-in?</label>
                            <button type="submit" class="red-btn">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>