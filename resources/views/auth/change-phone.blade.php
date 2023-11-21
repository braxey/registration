<title>Forgot Password</title>
<script src="{{asset('js/dist/jquery.min.js')}}"></script>
<script type="module" src="{{ asset('js/auth/change-phone.js') }}"></script>
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img class="mx-auto" src="{{ asset('images/kabc-logo.png') }}" alt="Custom Logo" style="width: 150px;">
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Enter a new phone number to verify.') }}
        </div>

        <form method="POST" action="{{ route('change-phone') }}" id="change-phone-form">
            @csrf

            <div class="block">
                <x-label for="phone_number" value="{{ __('Phone Number') }}" />
                <x-input id="phone_number" class="block mt-1 w-full" type="text" name="phone_number" required autofocus />
            </div>

            <p style="color:red; display:none" id="invalid-number">The given phone number is invalid.</p>
            <p style="color:red; display:none" id="existing-account">An account already exists with the given number.</p>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Submit phone number') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
