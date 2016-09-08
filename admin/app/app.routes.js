(function () {
  angular
    .module('pkg.abuse')
    .config(routeConfig)
    ;

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
