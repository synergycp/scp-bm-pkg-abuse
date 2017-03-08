(function () {
  angular
    .module('pkg.abuse.report')
    .service('PkgAbuseReportModal', PkgAbuseReportModalService)
  ;

  /**
   * @ngInject
   */
  function PkgAbuseReportModalService(RouteHelpers, _, Select, Modal) {
    var PkgAbuseReportModal = this;
    PkgAbuseReportModal.assign = assign;
    var pkg = RouteHelpers.package('abuse');

    function loadLang() {
      return RouteHelpers.loadLang(
        'pkg:abuse:admin:report'
      );
    }

    function assign(items) {
      loadLang();
      var selectedClient = null, selectedServer = null;
      items = _.map(items, function (report) {
        report.name = report.addr;

        selectedClient = selectedClient || report.client;
        selectedServer = selectedServer || report.server;

        return report;
      });

      var client = Select('client');
      var server = Select('server');
      client.on('change', filterServers);
      server.on('change', setClient);
      client.selected = selectedClient;
      filterServers();
      server.selected = selectedServer;
      var inputs = [{
        type: 'select',
        name: 'client',
        lang: "client",
        select: client,
        nullable: true,
      }, {
        type: 'select',
        name: 'server',
        lang: "server",
        select: server,
        nullable: true,
      }];

      return Modal
        .confirm(items, 'pkg.abuse.admin.report.modal.assign', {
          submitClass: 'btn-info',
        })
        .inputs(inputs)
        .open()
        .result
        .then(patch)
        ;

      function patch() {
        return pkg
          .api()
          .all('report')
          .one(_.map(items, 'id').join(','))
          .patch({
            client_id: client.getSelected('id') || null,
            server_id: server.getSelected('id') || null,
          })
          ;
      }

      function filterServers() {
        server.selected = null;
        server.clearFilter('client_id').filter({
          client_id: client.selected ? client.selected.id : undefined,
        }).load();
      }

      function setClient() {
        if (
          server.selected &&
          server.selected.client &&
          server.selected.client.id != (client.selected || {}).id
        ) {
          client.selected = server.selected.client;
          var selected = server.selected;
          filterServers();
          server.selected = selected;
        }
      }
    }
  }
})();
