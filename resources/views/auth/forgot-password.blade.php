<title>{{ __('Forgot Password') }}</title>
<script type="text/javascript" src="{{ version(mix('js/ResetPassword.js')) }}"></script>
<x-guest-layout>
    <div id="forgot_password_container">
        <x-authentication-card>
            <x-slot name="logo">
                <img class="mx-auto" src="{{ version('images/kabc-logo.png') }}" alt="Custom Logo" style="width: 150px;">
            </x-slot>

            <div class="mb-4 text-sm text-gray-600">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will let you choose a new one.') }}
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('forgot-password.check-email') }}" id="forgot_password_form">
                @csrf

                <div class="block">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
                </div>

                <p style="color:red; display:none" id="no_account_error">{{ __('No account exists with the given email.') }}</p>

                <div class="flex items-center justify-end mt-4">
                    <x-button>
                        {{ __('Email Me A Verification Code') }}
                    </x-button>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>
