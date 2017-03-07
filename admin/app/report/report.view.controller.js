(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .controller('ReportViewCtrl', ReportViewCtrl)
    ;

  /**
   * View Report Controller
   *
   * @ngInject
   */
  function ReportViewCtrl(RouteHelpers, $stateParams, _, EventEmitter, $uibModal) {
    var vm = this;
    var pkg = RouteHelpers.package('abuse');
    var $api = RouteHelpers
      .package('abuse')
      .api()
      .all('report')
      .one(''+$stateParams.id)
      ;

    vm.event = EventEmitter();
    vm.report = {
      id: $stateParams.id,
    };
    vm.logs = {
      filter: {
        target_type: 'pkg.abuse.report',
        target_id: $stateParams.id,
      },
      refresh: refreshLogs,
    };
    vm.toggleResolve = toggleResolve;
    vm.assignClient = assignClient;
    vm.assignServer = assignServer;
    console.log(vm.report.server);

    activate();

    //////////

    function activate() {
      $api.get()
        .then(storeCurrent)
        ;
    }

    function toggleResolve() {
      return $api.patch({
        is_resolved: !vm.report.date_resolved,
      }).then(storeCurrent).then(refreshLogs);
    }

    function refreshLogs() {
      vm.event.fire('change');
    }

    function storeCurrent(curr) {
      _.assign(vm.report, curr);
    }

    function assignClient() {
      RouteHelpers.loadLang('pkg:abuse:admin:report');

      var modal = $uibModal.open({
        templateUrl: RouteHelpers.trusted(
          pkg.asset('admin/report/modal/view/client/modal.assign.html')
        ),
        controller: 'PkgAbuseReportModalAssignClientCtrl',
        bindToController: true,
        controllerAs: 'modal',
        resolve: {
          items: _.return($stateParams.id),
        },
      });

      return modal.result.then(function (result) {
        return $api.patch({
          client_id: result.client ? result.client.id : null
        }).then(storeCurrent).then(refreshLogs);
      });
    }

    function assignServer() {
      RouteHelpers.loadLang('pkg:abuse:admin:report');

      var modal = $uibModal.open({
        templateUrl: RouteHelpers.trusted(
          pkg.asset('admin/report/modal/view/server/modal.assign.html')
        ),
        controller: 'PkgAbuseReportModalAssignServerCtrl',
        bindToController: true,
        controllerAs: 'modal',
        resolve: {
          items: _.return($stateParams.id),
        },
      });

      return modal.result.then(function (result) {
        return $api.patch({
          server_id: result.server ? result.server.id : null
        }).then(storeCurrent).then(refreshLogs);
      });
    }
  }
})();
