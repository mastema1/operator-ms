<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- Universal Opacity Decay Feature -->
        <link rel="stylesheet" href="{{ asset('css/opacity-decay-support.css') }}">
        <script src="{{ asset('js/opacity-decay.js') }}"></script>
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-gray-800">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
        
        <!-- Initialize Universal Opacity Decay System -->
        <script>
            // Initialize the opacity decay system with the start date from backend
            document.addEventListener('DOMContentLoaded', function() {
                const startDate = '{{ $opacityDecayStartDate }}';
                window.initOpacityDecay(startDate);
                
                // Optional: Log system status for debugging
                console.log('🎨 Opacity Decay System initialized with start date:', startDate);
            });
        </script>
    </body>
</html>
