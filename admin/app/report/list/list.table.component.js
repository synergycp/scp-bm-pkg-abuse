(function () {
  'use strict';

  angular
    .module('pkg.abuse.report.list')
    .component('pkgAbuseReportTable', {
      require: {
        list: '\^list',
      },
      bindings: {
        showDate: '=?',
        showIp: '=?',
        showServer: '=?',
        showClient: '=?',
        showUpdated: '=?',
        showFrom: '=?',
        showActions: '=?',
      },
      controller: 'PkgAbuseReportTableCtrl as table',
      transclude: true,
      templateUrl: templateUrl,
    })
    .controller('PkgAbuseReportTableCtrl', PkgAbuseReportTableCtrl)
    ;

  /**
   * @ngInject
   */
  function templateUrl(RouteHelpers) {
    return RouteHelpers
      .package('abuse')
      .trustedAsset('admin/report/list/list.table.html')
      ;
  }

  /**
   * @ngInject
   */
  function PkgAbuseReportTableCtrl() {
    var table = this;

    table.$onInit = init;

    ///////////

    function init() {
      _.defaults(table, {
        showDate: true,
        showIp: true,
        showServer: true,
        showClient: true,
        showUpdated: true,
        showFrom: true,
        showActions: true,
      });
    }
  }
})();
