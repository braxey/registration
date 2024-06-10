<x-app-layout>
    <html>
        <head>
            <title>{{ __('Create Walk-In') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/WalkIn.js')) }}"></script>
        </head>
        <body>
            <div id="create_walk_in_container" class="flex justify-center items-center text-center">
                <div class="container form-container">
                    <h1>{{ __('Create Walk-In') }}</h1>

                    <form action="{{ route('walk-in.create') }}" method="POST" id="create_walk_in_form">
                        @csrf

                        <div class="form-group">
                            <label for="name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('Email (optional)') }}</label>
                            <input type="email" name="email" id="email" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="slots">{{ __('Slots') }}</label>
                            <input type="text" name="slots" id="slots" class="form-control" value="1" required>
                        </div>

                        <div class="form-group">
                            <label for="desired_time">{{ __('Desired Time') }}</label>
                            <?php $startTime = date('Y-m-d\TH:i', strtotime(now('EST'))); ?>
                            <input type="datetime-local" name="desired_time" id="desired_time" class="form-control" value="{{ $startTime }}" min="{{ $startTime }}">
                        </div>

                        <div class="form-group">
                            <label for="notes">{{ __('Notes (optional)') }}</label>
                            <input type="text" name="notes" id="notes" class="form-control">
                        </div>

                        <button type="submit" class="grn-btn">{{ __('Create') }}</button>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>