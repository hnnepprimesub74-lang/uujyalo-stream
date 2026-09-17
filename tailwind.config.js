import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Netflix-style red — primary accent, futuristic/modern CTA color.
                brand: {
                    50: '#fdf1f1',
                    100: '#fbdada',
                    200: '#f7b6b6',
                    300: '#f18787',
                    400: '#e94848',
                    500: '#e50914',
                    600: '#c60813',
                    700: '#a30710',
                    800: '#7a050c',
                    900: '#560408',
                    950: '#330205',
                },
                // Neon green — secondary accent, paired with red for gradients/highlights.
                green: {
                    50: '#eafff3',
                    100: '#c9ffe0',
                    200: '#97ffc4',
                    300: '#5cffa3',
                    400: '#22f080',
                    500: '#0fd86a',
                    600: '#0bb157',
                    700: '#0a8a46',
                    800: '#0a6b38',
                    900: '#08542d',
                    950: '#04331b',
                },
            },
            borderRadius: {
                glass: '28px',
            },
            boxShadow: {
                'glass-sm': '0 2px 12px rgba(0,0,0,0.25)',
                glass: '0 8px 32px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.06)',
                'glass-lg': '0 20px 60px rgba(0,0,0,0.45), inset 0 1px 0 rgba(255,255,255,0.07)',
            },
            transitionTimingFunction: {
                glass: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
        },
    },

    plugins: [forms],
};
