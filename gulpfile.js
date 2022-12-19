/**
* Gulpfile.
* Project Configuration for gulp tasks.
*/
var pkg = require('./package.json');
var project = pkg.name;
var slug = pkg.slug;
var version = pkg.version;
var projectURL = 'http://demo.merlinwp.dev/wp-admin/themes.php?page=merlin';

// Translations.
var text_domain = '@@textdomain';
var destFile = slug + '.pot';
var packageName = project;
var bugReport = pkg.author_uri;
var lastTranslator = pkg.author;
var team = pkg.author_shop;
var translatePath = './languages/' + destFile;
var translatableFiles = ['./**/*.php', '!merlin-config-sample.php', '!merlin-filters-sample.php'];

// Styles.
var merlinStyleSRC = './assets/scss/merlin.scss'; // Path to main .scss file.
var merlinStyleDestination = './assets/css/'; // Path to place the compiled CSS file.
var merlinCssFiles = './assets/css/**/*.css'; // Path to main .scss file.
var merlinStyleWatchFiles = './assets/scss/**/*.scss'; // Path to all *.scss files inside css folder and inside them.

// Scripts.
var merlinScriptSRC = './assets/js/merlin.js'; // Path to JS custom scripts folder.
var merlinScriptDestination = './assets/js/'; // Path to place the compiled JS custom scripts file.
var merlinScriptFile = 'merlin'; // Compiled JS file name.
var merlinScriptWatchFiles = './assets/js/*.js'; // Path to all *.scss files inside css folder and inside them.

// Watch files.
var projectPHPWatchFiles = ['./**/*.php', '!_dist'];

// Build files.
var buildFiles = ['./**', '!node_modules/**', '!dist/', '!demo/**', '!composer.json', '!composer.lock', '!.gitattributes', '!phpcs.xml', '!package.json', '!package-lock.json', '!gulpfile.js', '!LICENSE', '!README.md', '!assets/scss/**', '!merlin-config-sample.php', '!merlin-filters-sample.php', '!CODE_OF_CONDUCT.md'];
var buildDestination = './dist/merlin/';
var distributionFiles = './dist/merlin/**/*';

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
var gulp = require('gulp');
var autoprefixer = require('gulp-autoprefixer');
var browserSync = require('browser-sync').create();
var cache = require('gulp-cache');
var cleaner = require('gulp-clean');
var copy = require('gulp-copy');
var csscomb = require('gulp-csscomb');
var filter = require('gulp-filter');
var lineec = require('gulp-line-ending-corrector');
var minifycss = require('gulp-clean-css');
var notify = require('gulp-notify');
var reload = browserSync.reload;
var rename = require('gulp-rename');
var replace = require('gulp-replace-task');
var sass = require('gulp-sass')(require('sass'));
var sort = require('gulp-sort');
var uglify = require('gulp-uglify');
var wpPot = require('gulp-wp-pot');
var zip = require('gulp-zip');
var composer = require('gulp-composer');

/**
 * Development Tasks.
 */
gulp.task('clear_cache', function (cb) {
	cache.clearAll();
	cb();
});

gulp.task('browser_sync', function (cb) {
	browserSync.init({
		// Project URL.
		proxy: projectURL,

		// `true` Automatically open the browser with BrowserSync live server.
		// `false` Stop the browser from automatically opening.
		open: true,

		// Inject CSS changes.
		injectChanges: true,
	});
	cb();
});

gulp.task('styles', function (cb) {
	gulp.src(merlinStyleSRC)
		.pipe(sass({
			errLogToConsole: true,
			outputStyle: 'expanded',
			precision: 10
		}))
		.on('error', console.error.bind(console))
		.pipe(autoprefixer(AUTOPREFIXER_BROWSERS))
		.pipe(csscomb())
		.pipe(gulp.dest(merlinStyleDestination))
		.pipe(browserSync.stream())
		.pipe(rename({ suffix: '.min' }))
		.pipe(minifycss({
			maxLineLen: 10
		}))
		.pipe(gulp.dest(merlinStyleDestination))
		.pipe(browserSync.stream());
	cb();
});

gulp.task('scripts', function (cb) {
	gulp.src(merlinScriptSRC)
		.pipe(rename({
			basename: merlinScriptFile,
			suffix: '.min'
		}))
		.pipe(uglify())
		.pipe(lineec())
		.pipe(gulp.dest(merlinScriptDestination));
	cb();
});

gulp.task('watch_files', function (cb) {
	gulp.watch(projectPHPWatchFiles, reload);
	gulp.watch(merlinStyleWatchFiles, gulp.series('styles'));
	cb();
});

gulp.task("composer", function (cb) {
	composer({ "async": false });
	cb();
});

/**
 * Build Tasks.
 */
gulp.task('translations', function (cb) {
	gulp.src(translatableFiles)
		.pipe(sort())
		.pipe(wpPot({
			domain: text_domain,
			destFile: destFile,
			package: project,
			bugReport: bugReport,
			lastTranslator: lastTranslator,
			team: team
		}))
		.pipe(gulp.dest(translatePath));
	cb();
});

gulp.task('clean_dist', function () {
	return gulp.src(['./dist/*'], { read: false })
		.pipe(cleaner());
});

gulp.task('copy_to_dist', function () {
	return gulp.src(buildFiles)
		.pipe(copy(buildDestination));
});

gulp.task('variables', function () {
	return gulp.src(distributionFiles)
		.pipe(replace({
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
		.pipe(gulp.dest(buildDestination));
});

gulp.task('build_zip', function () {
	return gulp.src(buildDestination + '/**', { base: 'dist' })
		.pipe(zip('merlin.zip'))
		.pipe(gulp.dest('./dist/'));
});

gulp.task('clean_zip', function () {
	return gulp.src([buildDestination, '!/dist/' + slug + '-wp.zip'], { read: false })
		.pipe(cleaner());
});

gulp.task('done_notification', function () {
	return gulp.src('.')
		.pipe(notify({ message: 'Your build of ' + packageName + ' is complete.', onLast: true }));
});

gulp.task(
	'build',
	gulp.series(
		'clear_cache',
		'clean_dist',
		gulp.parallel(
			'styles',
			'scripts',
			'translations'
		),
		'variables',
		'copy_to_dist',
		'composer',
		'build_zip',
		'clean_zip',
		'done_notification'
	)
);

gulp.task(
	'watch',
	gulp.series(
		'clear_cache',
		gulp.parallel(
			'styles',
			'scripts',
		),
		'browser_sync',
		'watch_files'
	)
);
