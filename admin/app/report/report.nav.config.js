(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .constant('PkgAbuseReportNav', {
      text: "Abuse Reports",
      sref: "app.pkg.abuse.report.list",
      alertType: 'danger',
      refreshInterval: 10000,
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
  function NavRun(PkgAbuseReportNav, $interval, Auth, RouteHelpers) {
    var $reports = RouteHelpers
      .package('abuse')
      .api()
      .all('report')
      ;
    var interval;

    Auth.whileLoggedIn(startChecking, stopChecking);

    ///////////

    function startChecking() {
      stopChecking();
      load();

      interval = $interval(load, PkgAbuseReportNav.refreshInterval);
    }

    function stopChecking() {
      if (interval) {
        $interval.cancel(interval);
      }
    }

    function load() {
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
