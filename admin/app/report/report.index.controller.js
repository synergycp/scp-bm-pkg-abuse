(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .controller('ReportIndexCtrl', ReportIndexCtrl);

  /**
   * @ngInject
   */
  function ReportIndexCtrl(List, $uibModal, _, $state, $stateParams, RouteHelpers) {
    var vm = this;
    var pkg = RouteHelpers.package('abuse');

    vm.search = $stateParams.search || '';
    vm.tabs = {
      active: parseInt($stateParams.tab) || 0,
      items: [
        new Tab('pkg.abuse.admin.report.list.tab.ADMIN', {
          pending_admin: true,
        }),
        new Tab('pkg.abuse.admin.report.list.tab.CLIENT', {
          pending_client: true,
        }),
        new Tab('pkg.abuse.admin.report.list.tab.ARCHIVE', {
          archive: true,
        }),
      ],
      change: changeTab,
    };

    vm.tabs.items[vm.tabs.active].active = true;

    vm.logs = {
      filter: {
        target_type: 'abuse-report',
      },
    };
    vm.searchChange = onSearchChange;

    activate();

    ////////////

    function activate() {
      onSearchChange();
    }

    function changeTab(active) {
      $stateParams.tab = vm.tabs.active = active;
      $state.go('app.pkg.abuse.report.list', $stateParams);
    }

    function assignClientServerModal(items) {
      var modal = $uibModal.open({
        templateUrl: RouteHelpers.trusted(
          pkg.asset('report/modal/modal.assign.html')
        ),
        controller: 'PkgAbuseReportModalAssignCtrl',
        bindToController: true,
        controllerAs: 'modal',
        resolve: {
          items: function () {
            return items;
          }
        }
      });

      return modal.result.then(function (result) {
        return vm.tabs.items[$stateParams.tab].list.patch({
          client_id: result.client ? result.client.id : null,
          server_id: result.server ? result.server.id : null,
        }, items);
      });
    }

    function onSearchChange() {
      $stateParams.search = vm.search;
      $state.go('app.pkg.abuse.report.list', $stateParams);
      loadAllTabs();
    }

    function loadAllTabs() {
      _(vm.tabs.items)
        .map(syncFilters)
        .map(load)
        .value();

      function syncFilters(tab) {
        tab.list.filter({
          search: vm.search,
        });

        return tab;
      }
    }

    function setupList(isArchive) {
      var list = List('abuse');

      list.bulk.add(
        isArchive ? 'Mark Unresolved' : 'Mark Resolved',
        list.patch.bind(null, {
          is_resolved: !isArchive,
        })
      );
      list.bulk.add('Assign Client/Server', assignClientServerModal);

      return list;
    }

    function Tab(trans, filters) {
      var tab = this;

      tab.text = trans;
      tab.list = setupList(filters.archive).filter(filters);
      tab.list.on('change', function () {
        _(vm.tabs.items).without(tab).map(load).value();
      });
      tab.active = false;
    }

    function load(tab) {
      tab.list.load();

      return tab;
    }
  }
})();
