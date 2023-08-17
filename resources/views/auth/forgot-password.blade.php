<title>Forgot Password</title>
<script src="{{asset('js/dist/jquery.min.js')}}"></script>
<script type="module" src="{{ asset('js/auth/forgot-password.js') }}"></script>
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img src="{{ asset('images/kabc-logo.png') }}" alt="Custom Logo">
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Just let us know your phone number and we will let you choose a new one.') }}
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('forgot-password.check') }}" id="forgot-password-form">
            @csrf

            <div class="block">
                <x-label for="phone_number" value="{{ __('Phone Number') }}" />
                <x-input id="phone_number" class="block mt-1 w-full" type="text" name="phone_number" required autofocus />
            </div>

            <p style="color:red; display:none" id="invalid-number">The given phone number is invalid.</p>
            <p style="color:red; display:none" id="no-account">No account exists with the given number.</p>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Verify Phone Number for Reset Link') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
