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
  function ReportViewCtrl(RouteHelpers, $state, _, EventEmitter, PkgAbuseReportModal) {
    var vm = this;
    var pkg = RouteHelpers.package('abuse');
    var $api = pkg
      .api()
      .all('report')
      .one(''+$state.params.id)
      ;

    vm.event = EventEmitter();
    vm.report = {
      id: $state.params.id,
    };
    vm.logs = {
      filter: {
        target_type: 'pkg.abuse.report',
        target_id: $state.params.id,
      },
      refresh: refreshLogs,
    };
    vm.toggleResolve = toggleResolve;
    vm.assign = assign;

    activate();

    //////////

    function activate() {
      refresh();
    }

    function refresh() {
      return $api
        .get()
        .then(storeCurrent)
      ;
    }

    function toggleResolve() {
      var data = {
        is_resolved: !vm.report.date_resolved,
      };

      return $api
        .patch(data)
        .then(storeCurrent)
        .then(refreshLogs)
        ;
    }

    function refreshLogs() {
      vm.event.fire('change');
    }

    function storeCurrent(curr) {
      _.assign(vm.report, curr);

      return vm.report;
    }

    function assign() {
      return PkgAbuseReportModal
        .assign([vm.report])
        .then(refresh)
        ;
    }
  }
})();
