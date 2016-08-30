(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .constant('PkgAbuseReportNav', {
      text: "Abuse Reports",
      sref: "app.pkg.abuse.report.list",
      alertType: 'danger',
      propagateAlerts: true,
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
  function NavRun(PkgAbuseReportNav, $interval, Api, Auth) {
    var $reports = Api.all('abuse');
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
