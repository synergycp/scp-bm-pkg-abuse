(function () {
  angular
    .module('pkg.abuse.report.list')
    .config(routeConfig)
    ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('abuse');
    pkg
      .state('report.list', {
        url: '?tab&search',
        title: 'Reports',
        controller: 'PkgAbuseReportIndexCtrl as vm',
        templateUrl: pkg.asset('admin/report/list/list.index.html'),
        reloadOnSearch: false,
      })
      ;
  }
})();
