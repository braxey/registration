<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img src="{{ asset('images/kabc-logo.png') }}" alt="Custom Logo">
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Your Walk Thru Bethlehem verification code is:') }}
        </div>

        <div class="mt-4 flex items-center">
            {{ $code }}
        </div>
    </x-authentication-card>
</x-guest-layout>
