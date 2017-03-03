(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .controller('PkgAbuseReportModalReplyCtrl', PkgAbuseReportModalReplyCtrl)
    ;

  /**
   * PkgAbuseReportModalAssign Controller
   *
   * @ngInject
   */
  function PkgAbuseReportModalReplyCtrl(Select, items) {
    var modal = this;

    console.log('select', Select);
    console.log('items', items);

    modal.comment = '';
    modal.submit = submit;

    activate();

    //////////

    function activate() {

    }

    function submit() {
      console.log('comment', modal.comment)
      return modal.$close({
        comment: modal.comment,
      });
    }
  }
})();
