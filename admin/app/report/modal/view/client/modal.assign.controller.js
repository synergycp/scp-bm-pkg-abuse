(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .controller('PkgAbuseReportModalAssignClientCtrl', PkgAbuseReportModalAssignClientCtrl)
    ;

  /**
   * PkgAbuseReportModalAssign Controller
   *
   * @ngInject
   */
  function PkgAbuseReportModalAssignClientCtrl(Select, items) {
    var modal = this;

    modal.client = Select('client');
    modal.submit = submit;

    activate();

    //////////

    function activate() {

    }

    function submit() {
      return modal.$close({
        client: modal.client.selected,
      });
    }
  }
})();
