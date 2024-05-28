/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
    "./node_modules/flowbite/**/*.js"
  ],
  safelist: [
    'text-blue-logo',
    'text-emerald',
    'blue-logo',
    'emerald',
    'text-green-500'
  ],
  theme: {
    fontFamily: {
      'title': ['montserrat', 'Helvetica', 'Arial', 'sans-serif'],
      'text': ['roboto', 'Helvetica', 'Arial', 'sans-serif']
    },
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      'rose-logo': '#f05166',
      'purple-logo': '#ba74ea',     
      'blue-logo': '#27b7e8', 
      'emerald' :'#a7f3d0',
      slate : {
        100: '#f1f5f9',
        200: '#e2e8f0',
        300: '#cbd5e1',
        400: '#94a3b8',
        500: '#64748b',
        600: '#475569'
      },
      amber : {
        50: '#fffbeb',
        100: '#fef3c7'
      },
      blueLogo : {
        100: 'rgba(39,183,232,0.1)', 
        200: 'rgba(39,183,232,0.2)', 
        300: 'rgba(39,183,232,0.3)', 
        400: 'rgba(39,183,232,0.4)', 
        500: 'rgba(39,183,232,0.5)', 
        600: 'rgba(39,183,232,0.6)', 
        700: 'rgba(39,183,232,0.7)', 
        800: 'rgba(39,183,232,0.8)', 
        900: 'rgba(39,183,232,0.9)'
      }


    },
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

