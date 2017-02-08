(function () {
  'use strict';

  angular
    .module('pkg.abuse.report.manage')
    .config(configurePanels)
    .factory('pkg.abuse.report.manage.panel', ManagePanel)
  ;

  /**
   * @ngInject
   */
  function configurePanels(ServerManageProvider) {
    ServerManageProvider.panels.left.add('pkg.abuse.report.manage.panel');
  }

  /**
   * @ngInject
   */
  function ManagePanel(RouteHelpers, ServerManage, List) {
    var list = List('pkg/abuse/report').filter({
      server: ServerManage.getServer().id,
    });
    list.refresh.now();
    return {
      templateUrl: RouteHelpers.trusted(
        RouteHelpers.package('abuse').asset('admin/report/manage/manage.panel.html')
      ),
      context: {
        list: list,
      },
    };
  }
})();
