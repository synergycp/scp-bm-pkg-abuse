(function () {
  'use strict';

  var URL = 'report/{{reportId}}/comment';

  angular
    .module('pkg.abuse.report.comments')
    .component('pkgAbuseReportComments', {
      require: {
      },
      bindings: {
        reportId: '=',
        onSubmit: '&?',
        onAdded: '&?',
      },
      controller: 'PkgAbuseReportCommentsCtrl as comments',
      transclude: true,
      templateUrl: function(RouteHelpers) {
        "ngInject";
        return RouteHelpers.trusted(
          RouteHelpers.package('abuse').asset(
            'admin/report/comments/comments.html'
          )
        );
      }
    })
    .controller('PkgAbuseReportCommentsCtrl', ReportCommentsCtrl)
    ;

  /**
   * @ngInject
   */
  function ReportCommentsCtrl(List, RouteHelpers) {
    var comments = this;
    var pkg = RouteHelpers.package('abuse');

    comments.body = '';

    comments.$onInit = init;
    comments.submit = submit;

    //////////

    function init() {
      comments.list = List(
        pkg.api().all(
          URL.replace('{{reportId}}', comments.reportId)
        )
      );
      comments.list.load();
    }

    function submit() {
      var data = formComment();

      (comments.onSubmit || angular.noop)(data);
      comments.list.create(data)
        .then(clearBody)
        .then(notifySubscribers)
        ;

      function notifySubscribers(data) {
        (comments.onAdded || angular.noop)(data);
      }
    }

    function clearBody() {
      comments.body = '';
    }

    function formComment() {
      return {
        body: comments.body,
      };
    }
  }
})();
