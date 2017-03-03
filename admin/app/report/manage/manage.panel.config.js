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
    ServerManageProvider.panels.left.after('notes', 'pkg.abuse.report.manage.panel');
  }

  /**
   * @ngInject
   */
  function ManagePanel(RouteHelpers, ServerManage, PkgAbuseReportList) {
    var list = PkgAbuseReportList().filter({
      server: ServerManage.getServer().id,
    });
    list.load();

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
