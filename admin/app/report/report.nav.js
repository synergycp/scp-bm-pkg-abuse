(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .constant('PkgAbuseReportNav', {
      text: "Abuse Reports",
      sref: "app.pkg.abuse.report.list",
      alertType: 'danger',
      propagateAlerts: true,
    })
    .config(NavConfig)
    .run(NavRun)
    ;

  /**
   * @ngInject
   */
  function NavConfig(NavProvider, PkgAbuseReportNav) {
    NavProvider
      .group('network')
      .item(PkgAbuseReportNav)
      ;
  }

  /**
   * @ngInject
   */
  function NavRun($rootScope, PkgAbuseReportNav, $interval, Api) {
    var $reports = Api.all('abuse');

    $interval(loadReports, 10000);
    loadReports();

    function loadReports() {
      return $reports
        .getList({
          per_page: 1,
          pending_admin: true,
        })
        .then(function(items) {
          PkgAbuseReportNav.alert = items.meta.total;
          PkgAbuseReportNav.group.syncAlerts();
        });
    }
  }
})();
