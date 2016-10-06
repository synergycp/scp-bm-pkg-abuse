(function () {
  angular
    .module('pkg.abuse')
    .config(routeConfig)
    ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('abuse');

    pkg.state('');
  }
})();
