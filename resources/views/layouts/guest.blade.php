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
        
        <!-- Universal Opacity Decay Feature -->
        <link rel="stylesheet" href="{{ asset('css/opacity-decay-support.css') }}">
        <script src="{{ asset('js/opacity-decay.js') }}"></script>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        
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
