const gulp = require( 'gulp' );
const sass = require('gulp-sass')(require('sass'));
const rename = require( 'gulp-rename' );
const cleanCSS = require('gulp-clean-css');
const uglify = require( 'gulp-uglify' );
const livereload = require( 'gulp-livereload' );

function compileSass() {
	return gulp.src( 'assets/scss/**/*.scss' )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( gulp.dest( 'assets/css' ) );
}

function cssMin() {
    return gulp.src( ['assets/css/**/*.css', '!assets/css/**/*.min.css'] )
        .pipe(rename({ extname: '.min.css' }))
        .pipe( cleanCSS() )
        .pipe( gulp.dest( 'assets/css' ) )
}

const css = gulp.series( compileSass, cssMin );

function js() {
	return gulp.src( [ 'assets/js/**/*.js', '!assets/js/**/*.min.js' ] )
		.pipe( uglify( {
			mangle: false
		} ) )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( gulp.dest( 'assets/js' ) )
};

function runLivereload() {
	livereload.listen();
	gulp.watch( [ 'assets/scss/**/*.scss' ], css );
	gulp.watch( [ 'assets/js/**/*.js', '!assets/js/**/*.min.js' ], js );
};

exports.js = js
exports.css = gulp.series( compileSass, cssMin );
exports.default = gulp.series( exports.css, js);
exports.watch = gulp.series( css, js, runLivereload );
