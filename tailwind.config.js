import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php', // Include PHP files for dynamic classes
    ],

    // Safelist critical status tag classes to prevent purging
    safelist: [
        'bg-red-500',
        'bg-green-500', 
        'bg-yellow-500',
        'text-white',
        'animate-pulse',
        // Status tag combinations
        {
            pattern: /bg-(red|green|yellow)-(500)/,
            variants: ['hover', 'focus'],
        },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
