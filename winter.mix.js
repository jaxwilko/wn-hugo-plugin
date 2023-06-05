const mix = require('laravel-mix');

mix.setPublicPath(__dirname)
   .js('assets/src/apex.js', 'assets/dist/apex.js');
