(function () {
  'use strict';

  var LANG = 'pkg.abuse.client.report.list';

  angular
    .module('pkg.abuse.report')
    .controller('PkgAbuseReportIndexCtrl', PkgAbuseReportIndexCtrl);

  /**
   * @ngInject
   */
  function PkgAbuseReportIndexCtrl(
    _,
    $q,
    List,
    $state,
    $uibModal,
    $stateParams,
    RouteHelpers
  ) {
    var vm = this;
    var pkg = RouteHelpers.package('abuse');

    vm.search = $stateParams.search || '';
    vm.tabs = {
      active: parseInt($stateParams.tab) || 0,
      items: [
        new Tab(LANG+'.tab.answered', {
          pending_admin: true,
        }),
        new Tab(LANG+'.tab.unanswered', {
          pending_client: true,
        }),
        new Tab(LANG+'.tab.archive', {
          archive: true,
        }),
      ],
      couldHaveResults: true,
      change: changeTab,
    };

    vm.tabs.items[vm.tabs.active].active = true;

    vm.searchChange = onSearchChange;

    activate();

    ////////////

    function activate() {
      onSearchChange()
        .then(function() {
          vm.tabs.couldHaveResults = !!_.some(vm.tabs.items, hasResults);
        });

      function hasResults(tab) {
        return tab.list.pages.total;
      }
    }

    function changeTab(active) {
      $stateParams.tab = vm.tabs.active = active;
      $state.go('app.pkg.abuse.report.list', $stateParams);
    }

    function assignClientServerModal(items) {
      var modal = $uibModal.open({
        templateUrl: RouteHelpers.trusted(
          pkg.asset('client/report/modal/modal.assign.html')
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

      return loadAllTabs();
    }

    function loadAllTabs() {
      return $q.all(
        _(vm.tabs.items)
          .map(syncFilters)
          .map(load)
          .value()
      );

      function syncFilters(tab) {
        tab.list.filter({
          search: vm.search,
        });

        return tab;
      }
    }

    function setupList(isArchive) {
      var list = List(pkg.api().all('report'));

      return list;
    }

    function Tab(trans, filters) {
      var tab = this;
      filters = filters || {};

      tab.text = trans;
      tab.list = setupList(filters.archive).filter(filters);
      tab.list.on('change', function () {
        _(vm.tabs.items)
          .without(tab)
          .map(load)
          .value()
          ;
      });
      tab.active = false;
    }

    function load(tab) {
      return tab.list.load();
    }
  }
})();
