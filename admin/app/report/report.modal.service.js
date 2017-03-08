(function () {
  angular
    .module('pkg.abuse.report')
    .service('PkgAbuseReportModal', PkgAbuseReportModalService)
  ;

  /**
   * @ngInject
   */
  function PkgAbuseReportModalService(RouteHelpers, _, Select, Modal, $q) {
    var PkgAbuseReportModal = this;
    var pkg = RouteHelpers.package('abuse');
    var $reports = pkg
      .api()
      .all('report')
      ;

    PkgAbuseReportModal.assign = assign;
    PkgAbuseReportModal.comment = comment;

    function loadLang() {
      return RouteHelpers.loadLang(
        'pkg:abuse:admin:report'
      );
    }

    function comment(items) {
      loadLang();

      return Modal
        .make({
          templateUrl: RouteHelpers.trusted(
            pkg.asset('admin/report/modal/modal.reply.html')
          ),
          controller: 'PkgAbuseReportModalReplyCtrl',
          bindToController: true,
          controllerAs: 'modal',
          resolve: {
            items: _.return(items),
          },
        })
        .open()
        .result
        .then(addComment)
        ;

      function addComment(result) {
        return $q.all(
          _.map(items, function(item) {
            return $reports
              .one(''+item.id)
              .post('comment', formComment(result.comment))
            ;
          })
        );
      }
    }

    function formComment(comment) {
      return {
        body: comment,
      };
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
        return $reports
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
