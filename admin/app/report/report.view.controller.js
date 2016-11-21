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
  function ReportViewCtrl(RouteHelpers, $stateParams, _, EventEmitter) {
    var vm = this;
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
  }
})();
