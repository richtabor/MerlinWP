/**
* Gulpfile.
* Project Configuration for gulp tasks.
*/

var pkg                     = require('./package.json');
var project                 = pkg.name;
var slug                    = pkg.slug;
var projectURL              = 'http://demo.merlinwp.dev/wp-admin/themes.php?page=merlin';

// Translations.
var text_domain             = '@@textdomain';
var destFile                = slug+'.pot';
var packageName             = project;
var bugReport               = pkg.author_uri;
var lastTranslator          = pkg.author;
var team                    = pkg.author_shop;

// Styles.
var merlinStyleSRC          = './merlin/assets/scss/merlin.scss'; // Path to main .scss file.
var merlinStyleDestination  = './merlin/assets/css/'; // Path to place the compiled CSS file.
var merlinCssFiles          = './merlin/assets/css/**/*.css'; // Path to main .scss file.
var merlinStyleWatchFiles   = './merlin/assets/scss/**/*.scss'; // Path to all *.scss files inside css folder and inside them.

// Scripts.
var merlinScriptSRC             = './merlin/assets/js/*.js'; // Path to JS custom scripts folder.
var merlinScriptDestination     = './merlin/assets/js/'; // Path to place the compiled JS custom scripts file.
var merlinScriptFile            = 'merlin'; // Compiled JS file name.
var merlinScriptWatchFiles  	= './merlin/assets/js/*.js'; // Path to all *.scss files inside css folder and inside them.

// Watch files paths.
var projectPHPWatchFiles    = ['./**/*.php', '!_dist', '!_dist/**', '!_dist/**/*.php', '!_demo', '!_demo/**','!_demo/**/*.php'];

// Browsers you care about for autoprefixing. https://github.com/ai/browserslist
const AUTOPREFIXER_BROWSERS = [
'last 2 version',
'> 1%',
'ie >= 9',
'ie_mob >= 10',
'ff >= 30',
'chrome >= 34',
'safari >= 7',
'opera >= 23',
'ios >= 7',
'android >= 4',
'bb >= 10'
];

/**
* Load Plugins.
*/
var gulp         = require('gulp');
var cache        = require('gulp-cache');
var sass         = require('gulp-sass');
var minifycss    = require('gulp-clean-css');
var autoprefixer = require('gulp-autoprefixer');
var lineec       = require('gulp-line-ending-corrector');
var rename       = require('gulp-rename');
var filter       = require('gulp-filter');
var notify       = require('gulp-notify');
var browserSync  = require('browser-sync').create();
var reload       = browserSync.reload;
var uglify       = require('gulp-uglify');
var wpPot        = require('gulp-wp-pot');
var sort         = require('gulp-sort');
var concat       = require('gulp-concat');

/**
* Tasks.
*/
gulp.task('clear', function () {
	cache.clearAll();
});

gulp.task( 'browser_sync', function() {
	browserSync.init( {

	// Project URL.
	proxy: projectURL,

	// `true` Automatically open the browser with BrowserSync live server.
	// `false` Stop the browser from automatically opening.
	open: true,

	// Inject CSS changes.
	injectChanges: true,

	});
});

gulp.task('styles', function () {
	gulp.src( merlinStyleSRC )

	.pipe( sass( {
		errLogToConsole: true,
		outputStyle: 'expanded',
		precision: 10
	} ) )

	.on( 'error', console.error.bind( console ) )

	.pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )

	.pipe( gulp.dest( merlinStyleDestination ) )

	.pipe( browserSync.stream() ) 

	.pipe( rename( { suffix: '.min' } ) )

	.pipe( minifycss( {
		maxLineLen: 10
	}))

	.pipe( gulp.dest( merlinStyleDestination ) )

	.pipe( browserSync.stream() )
});

gulp.task( 'scripts', function() {
	gulp.src( merlinScriptSRC )
	.pipe( concat( merlinScriptFile + '.min.js' ) )
	.pipe( lineec() )
	.pipe( gulp.dest( merlinScriptDestination ) )
	.pipe( rename( {
		basename: merlinScriptFile,
		suffix: '.min'
	}))
	.pipe( uglify() )
	.pipe( lineec() )
	.pipe( gulp.dest( merlinScriptDestination ) )
	
});

gulp.task( 'default', ['clear', 'styles', 'scripts', 'browser_sync' ], function () {
	gulp.watch( projectPHPWatchFiles, reload );
	gulp.watch( merlinStyleWatchFiles, [ 'styles' ] );
	// gulp.watch( merlinScriptWatchFiles, [ 'scripts' ] );
});
