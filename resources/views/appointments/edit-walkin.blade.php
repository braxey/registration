<x-app-layout>
    <html>
        <head>
            <title>Edit Walk-In</title>
            <script type="text/javascript" src="{{ version('js/dist/jquery.min.js') }}"></script>
            <script type="text/javascript" src="{{ version('js/dist/sweetalert2.all.min.js') }}"></script>
            <link rel="stylesheet" href="{{ version('css/main.css') }}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <h1>Edit Walk-In</h1>

                    <form action="{{ route('walk-in.edit', $walkIn->getId()) }}" method="POST" id="update-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $walkIn->getName() }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email (optional)</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $walkIn->getEmail() }}">
                        </div>

                        <div class="form-group">
                            <label for="slots">Slots</label>
                            <input type="text" name="slots" id="slots" class="form-control" value="{{ $walkIn->getNumberOfSlots() }}" required>
                        </div>

                        <div class="form-group">
                            <label for="desired_time">Desired Time</label>
                            <input type="datetime-local" name="desired_time" id="desired_time" class="form-control" value="{{ $walkIn->getDesiredTime() }}">
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (optional)</label>
                            <input type="text" name="notes" id="notes" class="form-control" value="{{ $walkIn->getNotes() }}">
                        </div>

                        <button type="submit" class="grn-btn">Update</button>
                    </form>
                    <script type="text/javascript" src="{{ version('js/appt/createedit-walkin.js') }}"></script>
                
                    <form action="{{ route('walk-in.delete', $walkIn->getId()) }}" method="POST" id="delete-form">
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