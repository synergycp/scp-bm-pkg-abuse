(function () {
  'use strict';

  angular
    .module('pkg.abuse.report.list')
    .component('pkgAbuseReportTable', {
      require: {
        list: '\^list',
      },
      bindings: {
        showName: '=?',
        showReserved: '=?',
        showIpEntities: '=?',
        showServers: '=?',
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
      .asset('client/report/list/list.table.html')
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
        showName: true,
        showReserved: true,
        showIpEntities: true,
        showServers: true,
        showActions: true,
      });
    }
  }
})();
