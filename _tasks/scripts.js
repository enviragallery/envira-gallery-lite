const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const webpack = require('webpack');

const named = require('vinyl-named-with-path');
const webpackStream = require('webpack-stream');
const webpack_config = require('./webpack/scripts.config.js');

const scriptFiles = [
	'assets/js/envira.js',
];

module.exports = function(gulp, plugins) {
	return function() {
		let stream = gulp

			.src(scriptFiles, { base: './' })
			.pipe(named())
			.pipe(webpackStream(webpack_config, webpack))
			.pipe(uglify())
			.pipe(
				rename(function(path) {
					path.dirname += '/min';
					path.basename += '-min';
					return path;
				}),
			)
			.pipe(gulp.dest('./'));

		return stream;
	};
};
