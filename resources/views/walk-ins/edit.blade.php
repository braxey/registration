<x-app-layout>
    <html>
        <head>
            <title>{{ __('Edit Walk-In') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/WalkIn.js')) }}"></script>
        </head>
        <body>
            <div id="edit_walk_in_container" class="flex justify-center items-center text-center">
                <div class="container form-container">
                    <h1>{{ __('Edit Walk-In') }}</h1>

                    <form action="{{ route('walk-in.edit', $walkIn->getId()) }}" method="POST" id="edit_walk_in_form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $walkIn->getName() }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('Email (optional)') }}</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $walkIn->getEmail() }}">
                        </div>

                        <div class="form-group">
                            <label for="slots">{{ __('Slots') }}</label>
                            <input type="text" name="slots" id="slots" class="form-control" value="{{ $walkIn->getNumberOfSlots() }}" required>
                        </div>

                        <div class="form-group">
                            <label for="desired_time">{{ __('Desired Time') }}</label>
                            <input type="datetime-local" name="desired_time" id="desired_time" class="form-control" value="{{ $walkIn->getDesiredTime() }}">
                        </div>

                        <div class="form-group">
                            <label for="notes">{{ __('Notes (optional)') }}</label>
                            <input type="text" name="notes" id="notes" class="form-control" value="{{ $walkIn->getNotes() }}">
                        </div>

                        <button type="submit" class="grn-btn">{{ __('Update') }}</button>
                    </form>
                
                    <form action="{{ route('walk-in.delete', $walkIn->getId()) }}" method="POST" id="delete_walk_in_form">
                        @csrf
                        <div class="form-group" style="text-align:left">
                            <label for="del">{{ __('Do you want to permanantly delete the walk-in?') }}</label>
                            <button type="submit" class="red-btn">{{ __('Delete') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>