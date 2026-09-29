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
                // Warm accents (Phase 10). Contrast on cream #F8F6F3:
                // sand-700 5.2, gold-600 4.3 (icons/lines), gold-700 6.2,
                // terracotta-600 5.5. gold-400/500 are decorative only on
                // light backgrounds (use gold-400 on charcoal: 6.0).
                sand: {
                    100: '#F3EBDD',
                    200: '#E8DAC0',
                    300: '#D9C39C',
                    500: '#B99A63',
                    700: '#7D6437',
                },
                gold: {
                    DEFAULT: '#B08A3E',
                    400: '#C9A45A',
                    500: '#B08A3E',
                    600: '#946F2B',
                    700: '#765720',
                },
                terracotta: {
                    DEFAULT: '#B9623F',
                    400: '#CF8062',
                    500: '#B9623F',
                    600: '#9C4E30',
                    700: '#7E3E26',
                },
            },
            // Fluid type scale: grows smoothly with the viewport and lands on the
            // previous fixed sizes (text-3xl/4xl/5xl) at desktop widths.
            fontSize: {
                hero: ['clamp(1.875rem, 1.3rem + 2.6vw, 3rem)', { lineHeight: '1.15' }],
                'page-title': ['clamp(1.75rem, 1.45rem + 1.3vw, 2.25rem)', { lineHeight: '1.2' }],
                'section-title': ['clamp(1.5rem, 1.3rem + 0.9vw, 1.875rem)', { lineHeight: '1.25' }],
            },
            // Self-hosted via @fontsource (resources/css/app.css); no font CDN.
            fontFamily: {
                sans: ['"Inter Variable"', 'Inter', ...defaultTheme.fontFamily.sans],
                arabic: ['"Cairo Variable"', 'Cairo', ...defaultTheme.fontFamily.sans],
                'display-ar': ['"Reem Kufi"', '"Cairo Variable"', ...defaultTheme.fontFamily.sans],
                'display-en': ['Marcellus', '"Inter Variable"', ...defaultTheme.fontFamily.serif],
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
