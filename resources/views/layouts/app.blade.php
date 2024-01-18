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
    <style>
        ::-webkit-scrollbar {
             width: 10px;
        }

        ::-webkit-scrollbar-track {
                box-shadow: inset 0 0 5px grey; 
                border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
                background: grey; 
                border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
                background: steelblue; 
        }
    </style>

</head>

<body class="font-sans antialiased" style="height:100vh; overflow:hidden;">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white dark:bg-gray-800 shadow" >
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" style="padding-top: 5px; padding-bottom:5px;">
                {{ $header }}
            </div>
        </header>
        @endif
        <!-- Page Content -->
        <div>
            <main style="padding-top: 10px;">
                {{ $slot }}
       
            </main>
        </div>

    </div>

    <div style="margin: 10px 0px;">
        
    </div>

    @livewireScripts
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-livewire-alert::scripts />
    @stack('scripts')
    
</body>

</html>