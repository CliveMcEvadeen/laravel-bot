<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    @livewire('chat-box')

    <style>
        .msg-pad{
            position: sticky;
            bottom: 0;
            padding: 10px;
            text-align: right;
        }

    </style>
</head>

<body class="font-sans antialiased" style="background-color:blue;" >
    {{-- <div>
        @include('layouts.message')
    </div> --}}

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900" >
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif
        <div>
        @include('livewire.chat-box.message-card') 
        </div>
        <!-- Page Content -->
        <main class="msg-pad">
            {{ $slot }}
        </main>

    </div>
    @livewireScripts
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />
    @stack('scripts')
</body>

</html>