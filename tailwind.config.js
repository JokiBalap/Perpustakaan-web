/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        midnight: {
          DEFAULT: '#121826',
          light: '#1e2530',
          dark: '#0a0d14'
        },
        parchment: {
          DEFAULT: '#F4F4F2',
          light: '#fafaf9',
          dark: '#e3e3df'
        },
        teal: {
          DEFAULT: '#398E8E',
          dark: '#1D5F5F',
          light: '#5fa3a3'
        },
        danger: {
          DEFAULT: '#C0392B',
          dark: '#962d22',
          light: '#d65245'
        },
        amber: {
          DEFAULT: '#D96B27',
          dark: '#b55418',
          light: '#e8884c'
        }
      },
      boxShadow: {
        book: '5px 5px 15px rgba(0, 0, 0, 0.4)',
        premium: '0 8px 32px 0 rgba(0, 0, 0, 0.2)'
      }
    },
  },
  plugins: [],
}
