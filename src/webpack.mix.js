const mix = require('laravel-mix');

// Basic pipeline
//=============================================================================
mix.js('resources/js/app.js', 'public').
    sass('resources/sass/app.scss', 'public');

// Additional configs
//=============================================================================
if (!mix.inProduction()) {
    mix.webpackConfig({
        devtool: 'source-map',
    }).sourceMaps();
}
