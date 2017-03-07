(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .controller('PkgAbuseReportModalAssignServerCtrl', PkgAbuseReportModalAssignServerCtrl)
    ;

  /**
   * PkgAbuseReportModalAssign Controller
   *
   * @ngInject
   */
  function PkgAbuseReportModalAssignServerCtrl(Select, items) {
    var modal = this;
  
    modal.server = Select('server');
    modal.submit = submit;

    activate();

    //////////

    function activate() {

    }

    function submit() {
      return modal.$close({
        server: modal.server.selected,
      });
    }
  }
})();
