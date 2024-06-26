<title>Register</title>
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img class="mx-auto" src="{{ version('images/kabc-logo.png') }}" alt="Custom Logo" style="width: 150px;">
            <p class="mx-auto pt-2">You must have an account to register for WTB.</p>
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="flex ">
                <div class="mr-2">
                    <x-label for="first_name" value="{{ __('First Name') }}" />
                    <x-input id="first_name" style="width: 100%;" class="block mt-1" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="first_name" />
                </div>

                <div>
                    <x-label for="last_name" value="{{ __('Last Name') }}" />
                    <x-input id="last_name" style="width: 100%;" class="block mt-1" type="text" name="last_name" :value="old('last_name')" required autofocus autocomplete="last_name" />
                </div>
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="sms-consent">
                    <div class="flex items-center">
                        <x-checkbox name="sms-consent" id="sms-consent" required />

                        <div class="ml-2">
                            {!! __('I agree to recieve emails. The emails will only be about WTB registration, and your information will not be shared.') !!}
                        </div>
                    </div>
                </x-label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
