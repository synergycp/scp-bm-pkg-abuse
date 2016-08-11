(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .config(SidebarConfig)
    ;

  /**
   * @ngInject
   */
  function SidebarConfig(SidebarProvider) {
    SidebarProvider.group('network').item({
      text: "Abuse Reports",
      sref: "app.pkg.abuse.report",
    });
  }
})();
