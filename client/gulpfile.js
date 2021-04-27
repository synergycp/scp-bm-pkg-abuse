var gulp = require("scp-ng-gulp")(require("gulp"));

gulp.require("settings").dir = __dirname;

var PATH = {
  PUBLIC: "../public/client/",
  MARKUP: "app/",
  SCRIPTS: "app/",
  ASSETS: "resources/assets/"
};
var js = {
  src: PATH.SCRIPTS,
  app: "app.js"
};

var versionManifest = gulp.require("version-manifest")({
  build: PATH.PUBLIC,
  files: [PATH.PUBLIC + "**/*.*"],
  package: "abuse/client"
});

var scripts = gulp.require("scripts");
gulp.task(
  "scripts",
  scripts.app({
    dest: PATH.PUBLIC + js.app,
    src: [PATH.SCRIPTS + "**/*.module.js", PATH.SCRIPTS + "**/*.js"]
  })
);

var templates = gulp.require("templates");
gulp.task(
  "templates",
  templates({
    src: [PATH.MARKUP + "**/*.pug"],
    dest: PATH.PUBLIC
  })
);

var copy = gulp.require("copy");
gulp.task(
  "copy",
  copy({
    src: PATH.ASSETS + "**/*.*",
    dest: PATH.PUBLIC,
    base: "resources"
  })
);

gulp.task("manifest", versionManifest);

gulp.task(
  "default",
  gulp.series([gulp.parallel(["copy", "templates", "scripts"]), "manifest"])
);
gulp.task("build", gulp.series(["default"]));
