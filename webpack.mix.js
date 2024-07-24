const mix = require('laravel-mix');

mix
    .sass('client/scss/main.scss', 'dist/css')
    .options({ processCssUrls: false });

mix
    .copy('client/images', 'dist/images');
