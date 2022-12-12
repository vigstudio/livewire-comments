let mix = require('laravel-mix');

mix.options({
    processCssUrls: false,
    clearConsole: true,
    terser: {
        extractComments: false,
    }
});

mix.disableNotifications();

const path = require('path');
const tailwindcss = require('tailwindcss');
const dist = 'vendor/livewire-comments';

mix.js('./resources/js/comments.js', './public/js');

mix.sass('./resources/sass/comments.scss', './public/css')
    .options({
        postCss: [tailwindcss('./tailwind.config.js')],
    });

mix.copyDirectory('public/js', '../../public/' + dist + '/js');
mix.copyDirectory('public/css', '../../public/' + dist + '/css');
