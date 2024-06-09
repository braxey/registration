<x-app-layout>
    <html>
        <head>
            <title>{{ __('Verify Email') }}</title>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
            <script type="text/javascript" src="{{ version(mix('js/ResetPassword.js')) }}"></script>
        </head>
        <body>
            <div id="forgot_password_verify_container" class="flex justify-center text-center">
                <div class="container form-container">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <h2><b>{{ __('Verify Email') }}</b></h2>
                            <br>
                            <p>{{ __('A verification code has been sent to:') }}</p><b>{{ $email }}</b>

                            <form action="{{ route('forgot-password.verify-token') }}" method="POST" id="forgot_password_verify_form">
                                @csrf

                                <div class="form-group">
                                    <label for="token">Token</label>
                                    <input type="text" name="token" id="token" class="form-control">
                                </div>

                                <p style="color:red; display:none" id="wrong_token_error">{{ __('The entered token is incorrect.') }}</p>
                                <p style="color:red; {{ $rate_limit ? '' : 'display: none;' }}" id="rate_limit_error">{{ __('You have reached the rate limit. Please try again later.') }}</p>

                                <button class="grn-btn" type="submit">{{ __('Verify') }}</button>
                            </form>

                            <form action="{{ route('forgot-password.resend-token') }}" method="POST">
                                @csrf
                                @method('POST')
                                <div class="form-group" style="text-align:center">
                                    <label for="resend">{{ __('Do you need a new token?') }}</label>
                                    <button type="submit" class="red-btn">{{ __('Resend Token') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>