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
      .state('report', {
        url: '/report',
        abstract: true,
        template: helper.dummyTemplate,
        resolve: helper.resolveFor(pkg.lang('client:report')),
      })
      .state('report.view', {
        url: '/:id',
        title: 'View Report',
        controller: 'ReportViewCtrl as vm',
        templateUrl: pkg.asset('client/report/report.view.html'),
      })
      .url('report/?([0-9]*)', mapReportUrl)
      .sso('report', function($state, options) {
        return mapReportUrl($state, options.id);
      })
      ;

    function mapReportUrl($state, id) {
      return $state.href('report.' + (id ? 'view' : 'list'), {
        id: id,
      });
    }
  }
})();
