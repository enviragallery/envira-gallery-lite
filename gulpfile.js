const gulp = require('gulp');
const plugins = require('gulp-load-plugins')();
const run = require('gulp-run-command').default;

/**
 * Helper method to load task.
 *
 * @since 1.0.0
 *
 * @param {*} task  Task to Load.
 */
function getTask(task) {
	return require('./_tasks/' + task)(gulp, plugins);
}

const scripts   = getTask('scripts');

gulp.task('scripts', scripts );

gulp.task('plugins', function () {
	return new Promise(function (resolve, reject) {
		console.log('gulp plugins');
		console.log(plugins);
		resolve();
	});
});