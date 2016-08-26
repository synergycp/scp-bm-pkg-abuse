(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .config(NavConfig)
    ;

  /**
   * @ngInject
   */
  function NavConfig(NavProvider) {
    NavProvider.group('network').item({
      text: "Abuse Reports",
      sref: "app.pkg.abuse.report",
    });
  }
})();
