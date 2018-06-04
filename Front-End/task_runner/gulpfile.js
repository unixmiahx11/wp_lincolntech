// ========================================
// Gulpfile
// ========================================
var theme_path  = '../../public_html/wp-content/themes/lincolntech/';
// load plugins
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    notify = require('gulp-notify'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    svgfragments = require('postcss-svg-fragments'),
    cssnano = require('cssnano'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    runSequence = require('run-sequence'),
    del = require('del');

var paths = {
    styles:  ['sass/**/*.scss'], //used for watch task
    criticalstyles : ['sass/critical/**/*.scss'], //used for criticalstyles task
    noncriticalstyles : ['sass/noncritical/**/*.scss'], //used for noncriticalstyles task
    scripts: [ //'../../public_html/wp-includes/js/jquery/jquery.js',
               //
               
               'js/vendor/jquery.min.js',
               //'../../public_html/wp-content/plugins/contact-form-7/includes/js/scripts.js',
               'js/vendor/jquery.validate.js',
               'js/vendor/custom-validate.js',
               //'js/vendor/jquery.inputmask.js',
               'js/vendor/jquery.mask.min.js',
               //'js/vendor/input-mask.js',
               'js/vendor/bootstrap/transition.js',
               'js/vendor/bootstrap/collapse.js',
               'js/vendor/magnific-popup.js',
               'js/script.js'
             ]
}

/**
 * Error notification settings for plumber
 */
var plumberErrorHandler = {
    errorHandler: notify.onError({
    message: "Error: <%= error.message %>"
  })
};

/**
 * Runnig criticalsass for critial above the fold css that is going to be inlined in head
**/
gulp.task('criticalsass', function() {
    var plugins = [
        autoprefixer({
            browsers: ['last 2 versions', 'ie 9', 'ie 10'],
            cascade: false
        })
    ];
    return gulp.src(paths.criticalstyles)
        .pipe(sourcemaps.init())
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(postcss( plugins ))
        .pipe(sourcemaps.write('../../css'))
        .pipe(gulp.dest('.tmp'))
});

/**
 * Runnig noncriticalsass for noncritial above the fold css that is going to be asynchronously loaded in the page
**/
gulp.task('noncriticalsass', function() {
    var plugins = [
        autoprefixer({
            browsers: ['last 2 versions', 'ie 9', 'ie 10'],
            cascade: false
        })
    ];
    return gulp.src(paths.noncriticalstyles)
        .pipe(sourcemaps.init())
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(postcss( plugins ))
        .pipe(sourcemaps.write('../../css'))
        .pipe(gulp.dest('.tmp'))
});


/**
 * Compile SVG Spritesheet css
*/
gulp.task('svgcompile', function(){

    var plugins = [
        svgfragments({})
    ];

    return gulp.src(['.tmp/critical.css', '.tmp/style.css'])
        .pipe(postcss(plugins))
        .pipe(gulp.dest('.tmp'))

}) 

/**
 * Run the Minify task which has a dependency of the criticalsass and noncriticalsass task
 * (critical and noncritical will be run first). Minify the temp CSS file and save to the
 * dist directory. Will change dest to theme directory
 */
gulp.task('minifycss', function() {

    var plugins = [
        cssnano({
            zindex: false
        })
    ];
    gulp.src('.tmp/critical.css')
        //.pipe(rename({suffix: '.min'}))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('../css'))
        .pipe(gulp.dest(theme_path + 'css/'));

    gulp.src('.tmp/style.css')
        //.pipe(rename({suffix: '.min'}))
        .pipe(postcss(plugins))
        .pipe(gulp.dest(theme_path))
        .pipe(gulp.dest('../css'));

});


/**
 * Concatenate scripts into a single file and minify the file.
 */
gulp.task('minify-scripts', function() {

    gulp.src(paths.scripts)
        .pipe(plumber(plumberErrorHandler))
        .pipe(sourcemaps.init())
        .pipe(concat('app.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(theme_path + 'js/'))
        .pipe(gulp.dest('../js'));
});


/**
 * Clean up the CSS files.
 */
gulp.task('clean-css', function(cb) {
    del([
        '.tmp/*.css',
        'public_html/dist/css/*.css',
        'public_html/dist/css/*.map',
        '../css/*.css',
        '../css/*.map'
    ], {force: true}, cb) //force true added to force to delete files outside this folder
});

/**
 * Clean up the JS files
 */
gulp.task('clean-js', function(cb) {
    del([
        '../js/*.js',
        '../js/*.map'
    ], {force: true}, cb)
});


/**
 * Set the task run order for style tasks
 */
gulp.task('styles', function(callback) {
    runSequence(
        'clean-css',
        'criticalsass',
        'noncriticalsass',
        'svgcompile',
        'minifycss',
    callback);
});

/**
 * Set the task run order for script tasks
 */
gulp.task('scripts', function() {
    runSequence(
        'clean-js',
        'minify-scripts'
    );
});


/**
 * Create the watch listener
 */
gulp.task('watch', function() {
    gulp.watch(paths.styles, ['styles']);
    gulp.watch(paths.scripts, ['scripts']);
});



/**
 * Set the default task to furst run styles, then scripts and then watch for
 * changes to the JS or SASS files.
 */
gulp.task('default', function (callback) {
    runSequence(
        'styles',
        'scripts',
        'watch',
        callback
    );
});