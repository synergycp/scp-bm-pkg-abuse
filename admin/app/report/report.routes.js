(function () {
  angular.module('pkg.abuse.report')
    .config(routeConfig);

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('abuse');
    pkg
      .state('app.pkg.abuse.report', {
        url: '/report',
        abstract: true,
        template: helper.dummyTemplate,
        resolve: helper.resolveFor(pkg.lang('admin:report')),
      })
      .state('app.pkg.abuse.report.list', {
        url: '?tab&search',
        title: 'Reports',
        controller: 'ReportIndexCtrl as vm',
        templateUrl: pkg.asset('admin/report/report.index.html'),
        reloadOnSearch: false,
      })
      .state('app.pkg.abuse.report.view', {
        url: '/:id',
        title: 'View Report',
        controller: 'ReportViewCtrl as vm',
        templateUrl: pkg.asset('admin/report/report.view.html'),
      })
      ;
  }
})();
