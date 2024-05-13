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
      'blue-logo': '#27b7e8', 
      slate : {
        100: '#f1f5f9',
        200: '#e2e8f0',
        300: '#cbd5e1'
      },
      amber : {
        50: '#fffbeb',
        100: '#fef3c7'
      }

    },
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

