(function () {
  'use strict';

  angular
    .module('pkg.abuse.report', [
      'scp.angle.layout.list',
      'scp.core.api',
      'pkg.abuse.report.comments',
      'pkg.abuse.report.list',
      'pkg.abuse.report.manage',
    ]);
})();
