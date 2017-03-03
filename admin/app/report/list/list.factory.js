(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .factory('PkgAbuseReportList', ListFactory);

  /**
   * @ngInject
   */
  function ListFactory (List, RouteHelpers, $uibModal, _) {
    var pkg = RouteHelpers.package('abuse');

    return function (isArchive) {
      var list = List(pkg.api().all('report'));

      list.bulk.add(
        isArchive ? 'Mark Unresolved' : 'Mark Resolved',
        list.patch.bind(null, {
          is_resolved: !isArchive,
        })
      );
      list.bulk.add('Assign Client/Server', assignClientServerModal);
      list.bulk.add('Bulk Reply', bulkReplyModal);
      /**
       * @ngInject
       */
      function assignClientServerModal(items) {
        RouteHelpers.loadLang('pkg:abuse:admin:report');

        var modal = $uibModal.open({
          templateUrl: RouteHelpers.trusted(
            pkg.asset('admin/report/modal/modal.assign.html')
          ),
          controller: 'PkgAbuseReportModalAssignCtrl',
          bindToController: true,
          controllerAs: 'modal',
          resolve: {
            items: _.return(items),
          },
        });

        return modal.result.then(function (result) {
          return list.patch({
            client_id: result.client ? result.client.id : null,
            server_id: result.server ? result.server.id : null,
          }, items);
        });
      }

      function bulkReplyModal(items) {
        RouteHelpers.loadLang('pkg:abuse:admin:report');

        var modal = $uibModal.open({
          templateUrl: RouteHelpers.trusted(
            pkg.asset('admin/report/modal/modal.reply.html')
          ),
          controller: 'PkgAbuseReportModalReplyCtrl',
          bindToController: true,
          controllerAs: 'modal',
          resolve: {
            items: _.return(items),
          },
        });

        return modal.result.then(function (result) {
          return list.patch({
              client_id: null,
              server_id: null,
              comment: result.comment ? result.comment : null,
          }, items);
        });
      }

      return list;
    };
  }
})();
