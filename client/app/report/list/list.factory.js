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
        RouteHelpers.loadLang('pkg:abuse:client:report');

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

          angular.forEach(items, function(item, key) {
            var comment = RouteHelpers
              .package('abuse')
              .api()
              .all('report')
              .one(''+item.id)
              ;
            comment.post('comment', formComment(result.comment));
          });
          return true;
        });
      }

      function formComment(comment) {
        return {
          body: comment,
        };
      }

      return list;
    };
  }

})();
