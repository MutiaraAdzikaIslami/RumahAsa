/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./app/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                forest: {
                    DEFAULT: '#2F5D4E',
                    light: '#3E6B5A',
                    dark: '#1E4D3A',
                },
                cream: {
                    DEFAULT: '#F7F5F0',
                    light: '#FFFFFF',
                    dark: '#E8E4DC',
                },
                terracotta: {
                    DEFAULT: '#B87333',
                    light: '#D49553',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'sans-serif'],
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            }
        },
    },
    plugins: [],
}