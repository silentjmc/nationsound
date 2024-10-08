/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    fontFamily: {
      'title': ['lato', 'Helvetica', 'Arial', 'sans-serif']
    },
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      'rose-logo': '#f05166',
      'purple-logo': '#ba74ea',     
      'blue-logo': '#27b7e8'
    },
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

