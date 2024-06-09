<title>{{ __('Reset Password') }}</title>
<script type="text/javascript" src="{{ version(mix('js/ResetPassword.js')) }}"></script>
<x-guest-layout>
    <div id="reset_password_container">
        <x-authentication-card>
            <x-slot name="logo">
                <img class="mx-auto" src="{{ version('images/kabc-logo.png') }}" alt="Custom Logo" style="width: 150px;">
            </x-slot>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('forgot-password.update-password') }}" id="reset_password_form">
                @csrf

                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                </div>

                <div class="mt-4">
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                <p style="color:red; display:none" id="no_match_error">{{ __('The password does not match the confirmation password.') }}</p>
                <p style="color:red; display:none" id="invalid_password_error">{{ __('The password must be at least 8 characters.') }}</p>

                <div class="flex items-center justify-end mt-4">
                    <x-button>
                        {{ __('Reset Password') }}
                    </x-button>
                </div>
            </form>
        </x-authentication-card>
    </div>
</x-guest-layout>
