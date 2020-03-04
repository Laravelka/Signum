const mix = require('laravel-mix');
const WorkboxPlugin = require('workbox-webpack-plugin');

mix.webpackConfig({
	resolve: {
		extensions: ['.js', '.vue'],
		alias: {
			'@': __dirname + '/resources'
		}
	}
});

mix.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')
	.copyDirectory('resources/img', 'public/img')
	.copy('resources/js/service-worker.js', 'public/service-worker.js')
	.version();
