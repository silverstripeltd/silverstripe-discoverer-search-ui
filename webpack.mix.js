const mix = require('laravel-mix');

mix
    .sass('src/scss/main.scss', 'dist/css')
    .options({ processCssUrls: false });

mix
    .copy('src/images', 'dist/images');
