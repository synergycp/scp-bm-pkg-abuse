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
    .run(NavRun)
    ;

  /**
   * @ngInject
   */
  function NavRun(Nav, PkgAbuseReportNav, $interval, Auth, Permission, RouteHelpers) {
    var $reports = RouteHelpers
      .package('abuse')
      .api()
      .all('report')
      ;
    var group = Nav.group('network');
    var interval;

    Auth.whileLoggedIn(checkPerms, stopChecking);

    ///////////

    function checkPerms() {
      Permission
        .ifHas('pkg.abuse.report.read')
        .then(startChecking)
      ;
    }

    function startChecking() {
      stopChecking();
      group.item(PkgAbuseReportNav);
      load();

      interval = $interval(load, PkgAbuseReportNav.refreshInterval);
    }

    function stopChecking() {
      group.remove(PkgAbuseReportNav);
      if (interval) {
        $interval.cancel(interval);
      }
    }

    function load() {
      /*return $reports
        .getList({
          per_page: 1,
          pending_admin: true,
        })
        .then(function(items) {
          PkgAbuseReportNav.alert = items.meta.total;
          PkgAbuseReportNav.group.syncAlerts();
        });*/
    }
  }
})();
