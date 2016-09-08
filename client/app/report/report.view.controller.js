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
  function ReportViewCtrl(Api, $stateParams, _, EventEmitter) {
    var vm = this;
    var $api = Api.one('abuse/'+$stateParams.id);

    vm.event = EventEmitter();
    vm.report = {
      id: $stateParams.id,
    };

    activate();

    //////////

    function activate() {
      $api.get()
        .then(storeCurrent)
        ;
    }

    function storeCurrent(curr) {
      _.assign(vm.report, curr);
    }
  }
})();
