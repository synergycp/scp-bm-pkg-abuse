var args = require('yargs').argv,
  path = require('path'),
  gulp = require('gulp'),
  $ = require('gulp-load-plugins')(),
  gulpsync = $.sync(gulp),
  browserSync = require('browser-sync').create(),
  reload = browserSync.reload,
  PluginError = $.util.PluginError,
  del = require('del'),
  _ = require('lodash');

// production mode (see build task)
var isProduction = false;
// styles sourcemaps
var useSourceMaps = false;
var isReloading = args.reload;

// Angular template cache
// Example:
//    gulp --nocache
var useCache = !args.nocache;

// ignore everything that begins with underscore
var hidden_files = '**/_*.*';
var ignored_files = '!' + hidden_files;

// MAIN PATHS
var paths = {
  app: '../public/client/',
  public: '../public/client/',
  markup: 'app/',
  scripts: 'app/',
  assets: 'assets/',
};

// SOURCES CONFIG
var source = {
  scripts: [
    paths.scripts + 'app.module.js',

    // modules
    paths.scripts + '**/*.module.js',
    paths.scripts + '**/*.js',
  ],
  templates: {
    views: [paths.markup + '**/*.jade'],
  },
  assets: [paths.assets + '**/*'],
};

// BUILD TARGET CONFIG
var build = {
  scripts: paths.public + 'js',
  assets: paths.public + 'assets',
  images: paths.public + 'assets/img',
  templates: {
    index: paths.public,
    views: paths.public,
    cache: paths.public + 'js/' + 'templates.js',
  }
};

// PLUGINS OPTIONS

var prettifyOpts = {
  indent_char: ' ',
  indent_size: 3,
  unformatted: ['a', 'sub', 'sup', 'b', 'i', 'u', 'pre', 'code']
};

var vendorUglifyOpts = {
  mangle: {
    except: ['$super'] // rickshaw requires this
  }
};

var jadeOptions = {
  doctype: 'html',
  basedir: __dirname
};

var tplCacheOptions = {
  root: 'app',
  filename: 'templates.js',
  //standalone: true,
  module: 'package.abuse',
  base: function (file) {
    return file.path.split('jade')[1];
  }
};

var cssnanoOpts = {
  safe: true,
  discardUnused: false, // no remove @font-face
  reduceIdents: false // no change on @keyframes names
};

//---------------
// TASKS
//---------------


// JS APP
gulp.task('scripts:app', function () {
  log('Building scripts..');
  // Minify and copy all JavaScript (except vendor scripts)
  var scripts = _.clone(source.scripts);
  if (isReloading) {
    scripts.push('reload.js');
  }

  return gulp.src(scripts)
    .on('error', handleError)
    .pipe($.if(useSourceMaps, $.sourcemaps.init()))
    .pipe($.concat('app.js'))
    .pipe($.ngAnnotate())
    .on('error', handleError)
    .pipe($.if(isProduction, $.uglify({
      preserveComments: 'some'
    })))
    .on('error', handleError)
    .pipe($.if(useSourceMaps, $.sourcemaps.write()))
    .pipe(gulp.dest(build.scripts))
    .pipe(reload({
      stream: true
    }));
});

// JADE
gulp.task('templates:views', function () {
  log('Building views.. ' + (useCache ? 'using cache' : ''));

  if (useCache) {

    return gulp.src(source.templates.views)
      .pipe($.jade(jadeOptions))
      .on('error', handleError)
      .pipe($.angularTemplatecache(tplCacheOptions))
      .pipe($.if(isProduction, $.uglify({
        preserveComments: 'some'
      })))
      .pipe(gulp.dest(build.scripts))
      .pipe(reload({
        stream: true
      }));
  } else {

    return gulp.src(source.templates.views)
      .pipe($.if(!isProduction, $.changed(build.templates.views, {
        extension: '.html'
      })))
      .pipe($.jade(jadeOptions))
      .on('error', handleError)
      .pipe($.htmlPrettify(prettifyOpts))
      .pipe(gulp.dest(build.templates.views))
      .pipe(reload({
        stream: true
      }));
  }
});

gulp.task('assets:raw', function () {
  return gulp
    .src(source.assets)
    .pipe(gulp.dest(build.assets))
    .pipe(reload({
      stream: true
    }));
});

//---------------
// WATCH
//---------------

// Rerun the task when a file changes
gulp.task('watch', function () {
  log('Watching source files..');

  gulp.watch(source.scripts, ['scripts:app']);
  gulp.watch(source.templates.views, ['templates:views']);
  gulp.watch(source.assets, ['assets:raw']);
});

// Serve files with auto reaload
gulp.task('browsersync', function () {
  log('Starting BrowserSync..');

  browserSync.init({
    notify: true,
  });
});

//---------------
// MAIN TASKS
//---------------

// build for production (minify)
gulp.task('build', gulpsync.sync([
  'prod',
  'assets'
]));

gulp.task('prod', function () {
  log('Starting production build...');
  isProduction = true;
});

// build with sourcemaps (no minify)
gulp.task('sourcemaps', ['usesources', 'default']);
gulp.task('usesources', function () {
  useSourceMaps = true;
});

// default (no minify)
gulp.task('default', gulpsync.sync([
  'assets',
  'watch'
]));

gulp.task('assets', [
  'scripts:app',
  'templates:views',
  'assets:raw'
]);


/////////////////////

function done() {
  log('************');
  log('* All Done * You can start editing your code, BrowserSync will update your browser after any change..');
  log('************');
}

// Error handler
function handleError(err) {
  log(err.toString());
  this.emit('end');
}

// log to console using
function log(msg) {
  $.util.log($.util.colors.blue(msg));
}
