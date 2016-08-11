(function () {
  'use strict';

  angular
    .module('pkg.abuse.report', [
      'app.layout.list',
      'app.core.api',
      'pkg.abuse.report.comments',
    ]);
})();
