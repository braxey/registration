<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img class="mx-auto" src="{{ version('images/kabc-logo.png') }}" alt="Custom Logo" style="width: 150px;">
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Your Walk Thru Bethlehem verification code is:') }}
        </div>

        <br>

        <div class="mt-4 flex justify-center items-center h-screen">
            <b>{{ $code }}</b>
        </div>
    </x-authentication-card>
</x-guest-layout>
