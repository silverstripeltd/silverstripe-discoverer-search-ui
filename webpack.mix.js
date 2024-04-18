const mix = require('laravel-mix');

mix.js('src/js/main.js', 'dist/js');

mix
    .sass('src/scss/main.scss', 'dist/css')
    .options({ processCssUrls: false });

mix
    .copy('src/images', 'dist/images')
    .copy('src/fonts', 'dist/fonts');
