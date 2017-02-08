(function () {
  'use strict';

  var LANG = 'pkg.abuse.admin.report.list';

  angular
    .module('pkg.abuse.report')
    .controller('PkgAbuseReportIndexCtrl', PkgAbuseReportIndexCtrl)
  ;

  /**
   * @ngInject
   */
  function PkgAbuseReportIndexCtrl(
    _,
    $q,
    PkgAbuseReportList,
    $state,
    $stateParams,
    RouteHelpers
  ) {
    var vm = this;
    var pkg = RouteHelpers.package('abuse');

    vm.search = $stateParams.search || '';
    vm.tabs = {
      active: parseInt($stateParams.tab) || 0,
      items: [
        new Tab(LANG+'.tab.admin', {
          pending_admin: true,
        }),
        new Tab(LANG+'.tab.client', {
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

    vm.logs = {
      filter: {
        target_type: 'pkg.abuse.report',
      },
    };
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

    function Tab(trans, filters) {
      var tab = this;

      tab.text = trans;
      tab.list = PkgAbuseReportList(filters.archive).filter(filters);
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
