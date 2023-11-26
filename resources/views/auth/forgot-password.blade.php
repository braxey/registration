<title>Forgot Password</title>
<script src="{{asset('js/dist/jquery.min.js')}}"></script>
<script type="module" src="{{ asset('js/auth/forgot-password.js') }}"></script>
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img class="mx-auto" src="{{ asset('images/kabc-logo.png') }}" alt="Custom Logo" style="width: 150px;">
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will let you choose a new one.') }}
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('forgot-password.check-email') }}" id="forgot-password-form">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" required autofocus />
            </div>

            <p style="color:red; display:none" id="no-account">No account exists with the given email.</p>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Email Me A Verification Code') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
