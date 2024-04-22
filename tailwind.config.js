/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    fontFamily: {
      'sans': ['Comfortaa', 'Montserrat', 'sans-serif'],
    },
    extend: {
      boxShadow: {
        'input': 'inset 2px 2px 5px 1px rgb(0 0 0 / 0.15)'
      }
    },
  },
  plugins: [
    require('tailwind-fontawesome')
  ],
}

