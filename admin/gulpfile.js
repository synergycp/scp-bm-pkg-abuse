var gulp = require('scp-ng-gulp')(require('gulp'));
var _ = require('lodash');

gulp.require('settings').dir = __dirname;

var PATH = {
  PUBLIC: 'public/',
  MARKUP: 'app/',
  SCRIPTS: 'app/',
  ASSETS: 'resources/assets/',
};
var scss = {
  img: 'assets/img/',
  src: 'resources/sass/',
};
var js = {
  src: PATH.SCRIPTS,
  app: 'app.js',
};
var appStyles = {
  src: [scss.src + '*.scss'],
  dest: PATH.PUBLIC+'css',
  base: scss.src,
  image: scss.image,
};
var themeStyles = _.assign({}, appStyles, {
  src: [scss.src + 'themes/*.scss'],
  sourceMaps: false,
});

var copy = gulp.require('copy');
var styles = gulp.require('styles');
var scripts = gulp.require('scripts');
var templates = gulp.require('templates');
var production = gulp.require('production');

gulp.task('styles', [
  'styles:app',
  'styles:app:rtl',
  'styles:themes',
]);
gulp.task('styles:app', styles.add(appStyles));
gulp.task('styles:app:rtl', styles.rtl(appStyles));
gulp.task('styles:themes', styles.add(themeStyles));

gulp.task('scripts', scripts.app({
  dest: PATH.PUBLIC + js.app,
  src: [
    PATH.SCRIPTS + '*.module.js',
    PATH.SCRIPTS + '**/*.module.js',
    PATH.SCRIPTS + '**/*.js'
  ],
}));

gulp.task('templates', templates({
  src: [PATH.MARKUP + '**/*.pug'],
  dest: PATH.PUBLIC,
}));

gulp.task('copy', copy({
  src: PATH.ASSETS+'**/*.*',
  dest: PATH.PUBLIC,
  base: 'resources',
}));

gulp.task('default', [
  'copy',
  'styles',
  'templates',
  'scripts',
]);

gulp.task('build', ['default']);
gulp.task('prod', production());
