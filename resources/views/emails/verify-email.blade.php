<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo"></x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Your Walk Thru Bethlehem verification code is:') }}
        </div>

        <br>

        <div class="mt-4 flex justify-center">
            <b>{{ $code }}</b>
        </div>
    </x-authentication-card>
</x-guest-layout>
