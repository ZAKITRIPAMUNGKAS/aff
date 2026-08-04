/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './admin/*.php',
    './*.html',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      colors: {
        teal: {
          DEFAULT: '#2fb8ae',
          50:  '#f0fdfb',
          100: '#ccfbf5',
          200: '#99f5eb',
          300: '#5ee9dc',
          400: '#2ecfc4',
          500: '#2fb8ae',
          600: '#1f8a82',
          700: '#1e726a',
          800: '#1e5c57',
          900: '#1e4d48',
          950: '#0b2e2b',
        },
      },
    },
  },
  plugins: [],
}
