import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                charcoal: {
                    DEFAULT: '#2B2B2B',
                    50: '#F2F2F2',
                    100: '#E0E0E0',
                    200: '#B8B8B8',
                    300: '#8F8F8F',
                    400: '#5C5C5C',
                    500: '#2B2B2B',
                    600: '#242424',
                    700: '#1C1C1C',
                    800: '#141414',
                    900: '#0A0A0A',
                },
                stone: {
                    DEFAULT: '#8B8680',
                    50: '#F5F4F3',
                    100: '#EBE9E7',
                    200: '#D5D1CC',
                    300: '#BEB9B2',
                    400: '#A6A099',
                    500: '#8B8680',
                    600: '#726D67',
                    700: '#59554F',
                    800: '#403D39',
                    900: '#282623',
                },
                cream: {
                    DEFAULT: '#F8F6F3',
                    100: '#FFFFFF',
                    200: '#F8F6F3',
                    300: '#EFEBE4',
                },
            },
            // Fluid type scale: grows smoothly with the viewport and lands on the
            // previous fixed sizes (text-3xl/4xl/5xl) at desktop widths.
            fontSize: {
                hero: ['clamp(1.875rem, 1.3rem + 2.6vw, 3rem)', { lineHeight: '1.15' }],
                'page-title': ['clamp(1.75rem, 1.45rem + 1.3vw, 2.25rem)', { lineHeight: '1.2' }],
                'section-title': ['clamp(1.5rem, 1.3rem + 0.9vw, 1.875rem)', { lineHeight: '1.25' }],
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                arabic: ['Cairo', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
