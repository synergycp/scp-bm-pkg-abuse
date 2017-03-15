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
  function ManagePanel(RouteHelpers, ServerManage, PkgAbuseReportList, Permission) {
    var context = {
      list: PkgAbuseReportList()
        .filter({
          server: ServerManage.getServer().id,
        }),
    };
    context.list.load();

    return {
      templateUrl: RouteHelpers.trusted(
        RouteHelpers.package('abuse').asset('client/report/manage/manage.panel.html')
      ),
      context: context,
    };
  }
})();
