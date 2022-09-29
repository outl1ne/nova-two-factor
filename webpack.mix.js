require('./nova.mix');

let mix = require('laravel-mix');
let tailwindcss = require('tailwindcss');

mix
  .setPublicPath('dist')
  .js('resources/js/entry.js', 'js')
  .vue({ version: 3 })
  .postCss('resources/sass/entry.css', 'css', [tailwindcss('tailwind.config.js')])
  .nova('outl1ne/nova-two-factor');
