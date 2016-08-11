(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .controller('PkgAbuseReportModalAssignCtrl', PkgAbuseReportModalAssignCtrl)
    ;

  /**
   * PkgAbuseReportModalAssign Controller
   *
   * @ngInject
   */
  function PkgAbuseReportModalAssignCtrl(Select, items) {
    var modal = this;

    modal.client = Select('client');
    modal.client.on('change', filterServers);
    modal.server = Select('server');
    modal.server.on('change', setClient);
    modal.submit = submit;

    activate();

    //////////

    function activate() {

    }

    function filterServers() {
      modal.server.selected = null;
      modal.server.clearFilter('client_id').filter({
        client_id: modal.client.selected ? modal.client.selected.id : undefined,
      }).load();
    }

    function setClient() {
      if (modal.server.selected && modal.server.selected.client && modal.server.selected.client.id != (modal.client.selected || {}).id) {
        modal.client.selected = modal.server.selected.client;
        var selected = modal.server.selected;
        filterServers();
        modal.server.selected = selected;
      }
    }

    function submit() {
      return modal.$close({
        client: modal.client.selected,
        server: modal.server.selected,
      });
    }
  }
})();
