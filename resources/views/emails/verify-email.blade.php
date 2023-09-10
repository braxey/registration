<x-guest-layout>
    <x-authentication-card>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Your Walk Thru Bethlehem verification code is:') }}
        </div>

        <br>

        <div class="mt-4 flex justify-center items-center h-screen">
            <b>{{ $code }}</b>
        </div>
    </x-authentication-card>
</x-guest-layout>
