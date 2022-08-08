var gulp = require( 'gulp' );
var gulpSass = require( 'gulp-sass' )(require('sass'));
var rename = require( 'gulp-rename' );
var cssmin = require( 'gulp-cssmin' );
var uglify = require( 'gulp-uglify' );
var livereload = require( 'gulp-livereload' );

function sass() {
	return gulp.src( 'assets/scss/**/*.scss' )
		.pipe( gulpSass.sync().on( 'error', gulpSass.logError ) )
		.pipe( gulp.dest( 'assets/css' ) )
		.pipe( livereload() );
};

function css () {
	return gulp.src( [ 'assets/css/**/*.css', '!assets/css/**/*.min.css' ] )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( cssmin() )
		.pipe( gulp.dest( 'assets/css' ) )
};

function js () {
	return gulp.src( [ 'assets/js/**/*.js', '!assets/js/**/*.min.js' ] )
		.pipe( uglify( {
			mangle: false
		} ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( gulp.dest( 'assets/js' ) )
		.pipe( livereload() );
};

exports.sass = sass;
exports.css = css;
exports.js = js;

exports.build = gulp.series( sass, css, js );

exports.watch = gulp.series( sass, css, js, () => {
        livereload.listen();
        gulp.watch( [ 'assets/scss/**/*.scss' ], gulp.series( sass, css ) );
        gulp.watch( [ 'assets/js/**/*.js' ], gulp.series( js ) );
    } );
