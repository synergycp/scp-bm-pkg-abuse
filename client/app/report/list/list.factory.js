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

      list.bulk.add('Bulk Reply', bulkReplyModal);

      /**
       * @ngInject
       */
      function bulkReplyModal(items) {
          RouteHelpers.loadLang('pkg:abuse:admin:report');

          var modal = $uibModal.open({
              templateUrl: RouteHelpers.trusted(
                  pkg.asset('client/report/modal/modal.reply.html')
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
