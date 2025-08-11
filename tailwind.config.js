import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        container: {
            center: true,
            padding: {
                DEFAULT: "1rem",
                md: "1.5rem",
                lg: "3rem",
            },
            screens: {
                xl: "1280px",
                "2xl": "1400px",
            },
        },
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    100: "hsl(var(--primary-100))",
                    200: "hsl(var(--primary-200))",
                    300: "hsl(var(--primary-300))",
                    400: "hsl(var(--primary-400))",
                    500: "hsl(var(--primary-500))",
                    600: "hsl(var(--primary-600))",
                    700: "hsl(var(--primary-700))",
                    800: "hsl(var(--primary-800))",
                    900: "hsl(var(--primary-900))",
                },
                secondary: {
                    100: "hsl(var(--secondary-100))",
                    200: "hsl(var(--secondary-200))",
                    300: "hsl(var(--secondary-300))",
                    400: "hsl(var(--secondary-400))",
                    500: "hsl(var(--secondary-500))",
                    600: "hsl(var(--secondary-600))",
                    700: "hsl(var(--secondary-700))",
                    800: "hsl(var(--secondary-800))",
                    900: "hsl(var(--secondary-900))",
                },
                success: {
                    100: "hsl(var(--success-100))",
                    200: "hsl(var(--success-200))",
                    300: "hsl(var(--success-300))",
                    400: "hsl(var(--success-400))",
                    500: "hsl(var(--success-500))",
                    600: "hsl(var(--success-600))",
                    700: "hsl(var(--success-700))",
                    800: "hsl(var(--success-800))",
                    900: "hsl(var(--success-900))",
                },
                error: {
                    100: "hsl(var(--error-100))",
                    200: "hsl(var(--error-200))",
                    300: "hsl(var(--error-300))",
                    400: "hsl(var(--error-400))",
                    500: "hsl(var(--error-500))",
                    600: "hsl(var(--error-600))",
                    700: "hsl(var(--error-700))",
                    800: "hsl(var(--error-800))",
                    900: "hsl(var(--error-900))",
                },
                warning: {
                    100: "hsl(var(--warning-100))",
                    200: "hsl(var(--warning-200))",
                    300: "hsl(var(--warning-300))",
                    400: "hsl(var(--warning-400))",
                    500: "hsl(var(--warning-500))",
                    600: "hsl(var(--warning-600))",
                    700: "hsl(var(--warning-700))",
                    800: "hsl(var(--warning-800))",
                    900: "hsl(var(--warning-900))",
                },
                info: {
                    100: "hsl(var(--info-100))",
                    200: "hsl(var(--info-200))",
                    300: "hsl(var(--info-300))",
                    400: "hsl(var(--info-400))",
                    500: "hsl(var(--info-500))",
                    600: "hsl(var(--info-600))",
                    700: "hsl(var(--info-700))",
                    800: "hsl(var(--info-800))",
                    900: "hsl(var(--info-900))",
                }
            }
        },
    },
    plugins: [],
};
