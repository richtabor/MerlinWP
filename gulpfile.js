/**
* Gulpfile.
* Project Configuration for gulp tasks.
*/

var pkg                     = require('./package.json');
var project                 = pkg.name;
var slug                    = pkg.slug;
var version                 = pkg.version;
var projectURL              = 'http://demo.merlinwp.dev/wp-admin/themes.php?page=merlin';

// Translations.
var text_domain             = '@@textdomain';
var destFile                = slug+'.pot';
var packageName             = project;
var bugReport               = pkg.author_uri;
var lastTranslator          = pkg.author;
var team                    = pkg.author_shop;
var translatePath           = './languages';
var translatableFiles       = ['./**/*.php', '!merlin-config-sample.php', '!merlin-filters.php' ];

// Styles.
var merlinStyleSRC          = './assets/scss/merlin.scss'; // Path to main .scss file.
var merlinStyleDestination  = './assets/css/'; // Path to place the compiled CSS file.
var merlinCssFiles          = './assets/css/**/*.css'; // Path to main .scss file.
var merlinStyleWatchFiles   = './assets/scss/**/*.scss'; // Path to all *.scss files inside css folder and inside them.

// Scripts.
var merlinScriptSRC         = './assets/js/*.js'; // Path to JS custom scripts folder.
var merlinScriptDestination = './assets/js/'; // Path to place the compiled JS custom scripts file.
var merlinScriptFile        = 'merlin'; // Compiled JS file name.
var merlinScriptWatchFiles  = './assets/js/*.js'; // Path to all *.scss files inside css folder and inside them.

// Watch files.
var projectPHPWatchFiles    = ['./**/*.php', '!_dist'];

// Build files.
var buildFiles      	    = ['./**', '!dist/', '!.gitattributes', '!phpcs.ruleset.xml', '!package.json', '!gulpfile.js', '!LICENSE', '!README.md', '!assets/scss/**', '!merlin-config-sample.php', '!merlin-filters.php' ];
var buildDestination        = './dist/merlin/';
var distributionFiles       = './dist/merlin/**/*';

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
var autoprefixer = require('gulp-autoprefixer');
var browserSync  = require('browser-sync').create();
var cache        = require('gulp-cache');
var cleaner      = require('gulp-clean');
var concat       = require('gulp-concat');
var copy         = require('gulp-copy');
var csscomb      = require('gulp-csscomb');
var filter       = require('gulp-filter');
var gulp         = require('gulp');
var lineec       = require('gulp-line-ending-corrector');
var minifycss    = require('gulp-clean-css');
var notify       = require('gulp-notify');
var reload       = browserSync.reload;
var rename       = require('gulp-rename');
var replace      = require('gulp-replace-task');
var runSequence  = require('gulp-run-sequence');
var sass         = require('gulp-sass');
var sort         = require('gulp-sort');
var uglify       = require('gulp-uglify');
var wpPot        = require('gulp-wp-pot');
var zip          = require('gulp-zip');

/**
 * Development Tasks.
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

	.pipe( csscomb() )

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
});

/**
 * Build Tasks.
 */

gulp.task( 'build-translate', function () {

	gulp.src( translatableFiles )

	.pipe( sort() )
	.pipe( wpPot( {
		domain        : text_domain,
		destFile      : destFile,
		package       : project,
		bugReport     : bugReport,
		lastTranslator: lastTranslator,
		team          : team
	} ))
	.pipe( gulp.dest( translatePath ) )

});

gulp.task( 'build-clean', function () {
	return gulp.src( ['./dist/*'] , { read: false } )
	.pipe(cleaner());
});

gulp.task( 'build-copy', ['build-clean'], function() {
    return gulp.src( buildFiles )
    .pipe( copy( buildDestination ) );
});

gulp.task( 'build-clean-and-copy', ['build-clean', 'build-copy' ], function () { } );

gulp.task('build-variables', ['build-clean-and-copy'], function () {
	return gulp.src( distributionFiles )
	.pipe( replace( {
		patterns: [
		{
			match: 'pkg.version',
			replacement: version
		},
		{
			match: 'textdomain',
			replacement: pkg.textdomain
		}
		]
	}))
	.pipe( gulp.dest( buildDestination ) );
});

gulp.task( 'build-zip', ['build-variables'] , function() {
    return gulp.src( buildDestination+'/**' )
    .pipe( zip( 'merlin.zip' ) )
    .pipe( gulp.dest( './dist/' ) );
});

gulp.task( 'build-clean-after-zip', ['build-zip'], function () {
	return gulp.src( [ buildDestination, '!/dist/' + slug + '-wp.zip'] , { read: false } )
	.pipe(cleaner());
});

gulp.task( 'build-zip-and-clean', ['build-zip', 'build-clean-after-zip' ], function () { } );

gulp.task( 'build-notification', function () {
	return gulp.src( '' )
	.pipe( notify( { message: 'Your build of ' + packageName + ' is completed.', onLast: true } ) );
});

gulp.task('build', function(callback) {
	runSequence( 'clear', 'build-clean', ['styles', 'scripts', 'build-translate'], 'build-clean-and-copy', 'build-variables', 'build-zip-and-clean', 'build-notification', callback);
});
