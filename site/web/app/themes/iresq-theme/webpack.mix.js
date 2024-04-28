const mix = require('laravel-mix');
const path = require('path');
const tailwindcss = require('tailwindcss');
const ImageminPlugin = require('imagemin-webpack-plugin').default;

mix.js('resources/assets/scripts/main.js', 'js')
  .ts('resources/assets/scripts/vue.ts', 'js').vue()
  .js('resources/assets/scripts/customizer.js', 'js');

mix.sass('resources/assets/styles/main.scss', 'dist/css')
  .options({
    postCss: [tailwindcss('./resources/assets/styles/tailwind.js')],
  });

mix.sass('resources/assets/styles/critical.scss', 'dist/css');

mix.copyDirectory('resources/assets/images/**', 'dist/images');

mix.autoload({ jquery: ['$', 'jQuery', 'window.jQuery'] });
mix.sourceMaps();

mix.webpackConfig({
  resolve: {
    alias: {
      '~': path.join(__dirname, './'),
      '@scripts': path.join(__dirname, './resources/assets/scripts'),
    },
  },
  plugins: [
    new ImageminPlugin({
      disable: process.env.NODE_ENV !== 'production',
      pngquant: {
        quality: '90-95',
      },
      test: /\.(jpe?g|png|gif|svg)$/i,
    }),
  ],
});
