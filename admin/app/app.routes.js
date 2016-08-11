(function () {
  angular.module('app.core.routes')
    .config(routeConfig);

  /**
   * @ngInject
   */
  function routeConfig($stateProvider, RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    $stateProvider
      .state('app.pkg.abuse', {
        url: '/abuse',
        abstract: true,
        template: helper.dummyTemplate,
      })
      ;
  }
})();
