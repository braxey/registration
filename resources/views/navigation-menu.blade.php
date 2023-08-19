@php
    use Illuminate\Support\Facades\Auth;
@endphp

@auth
    @php
        $user = Auth::user();
    @endphp
@else
    @php
        $user = 0;
    @endphp
@endauth

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body class="flex justify-center items-center h-screen">
@if (Route::has('login'))
    <div class="p-6 pt-6" style="width: 100%;">
        <nav class="justify-right">
            <!-- Primary Navigation Menu -->
            <div class=" mx-auto px-4 sm:px-6 lg:px-8 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 justify-right h-10" x-data="{ open: false }" style="max-width: 1000px">
                <div class="h-16">
                    <div>
                        <!-- Navigation Links -->
                        <div class="space-x-8 sm:-my-px sm:ml-10 sm:flex items-center flex justify-end">
                            @auth
                                @if(Request::url() !== url('/organization/1/edit') && !is_int($user) && $user->admin)
                                    <form action="{{ route('organization.edit', ['id' => 1]) }}" method="GET">
                                        @csrf
                                        @method('GET')
                                        <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Organization</button>
                                    </form>
                                @endif
                                @if(Request::url() !== url('/guestlist') && !is_int($user) && $user->admin)
                                    <form action="{{ route('appointments.guestlist') }}" method="GET">
                                        @csrf
                                        @method('GET')
                                        <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Guestlist</button>
                                    </form>
                                @endif
                                @if(Request::url() !== url('/walkin-waitlist') && !is_int($user) && $user->admin)
                                    <form action="{{ route('walk-in-waitlist.show') }}" method="GET">
                                        @csrf
                                        @method('GET')
                                        <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Walk-Ins</button>
                                    </form>
                                @endif
                            @endauth   
                            @if(Request::url('') !== url(''))
                                <form action="{{ route('welcome') }}" method="GET">
                                    @csrf
                                    @method('GET')
                                    <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Home</button>
                                </form>
                            @endif
                            @if(Request::url() !== url('/appointments'))
                                <form action="{{ route('appointments.index') }}" method="GET">
                                    @csrf
                                    @method('GET')
                                    <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Appointments</button>
                                </form>
                            @endif
                            @auth
                                @if(Request::url() !== url('/dashboard'))
                                    <form action="{{ route('dashboard') }}" method="GET">
                                        @csrf
                                        @method('GET')
                                        <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Dashboard</button>
                                    </form>
                                @endif
                            @else
                                <form action="{{ route('login') }}" method="GET">
                                    @csrf
                                    @method('GET')
                                    <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Login</button>
                                </form>
                                @if (Route::has('register'))
                                    <form action="{{ route('register') }}" method="GET">
                                        @csrf
                                        @method('GET')
                                        <button class="text-sm text-gray-500 dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300">Register</button>
                                    </form>
                                @endif
                            @endauth
                            @auth
                                @if(Request::url() === url('/dashboard'))
                                    <!-- Settings Dropdown -->
                                    <div class="sm:flex sm:items-center sm:ml-6">
                                        <x-dropdown align="right" width="48">
                                            <x-slot name="trigger">
                                                <button class="inline-flex items-center border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                                    <div>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>

                                                    <div class="ml-1">
                                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </button>
                                            </x-slot>

                                            <x-slot name="content">
                                                <x-dropdown-link :href="route('profile.show')">
                                                    {{ __('Profile') }}
                                                </x-dropdown-link>

                                                <!-- Authentication -->
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf

                                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                                        {{ __('Log Out') }}
                                                    </x-dropdown-link>
                                                </form>
                                            </x-slot>
                                        </x-dropdown>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
@endif
</body>
</html>
