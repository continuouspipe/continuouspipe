'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function($scope, $state, tide, summary) {
        $scope.tide = tide;
        $scope.summary = summary;

        var timeOutIdentifier = null,
            reloadSummaryIfRunning = function() {
            if ($scope.summary.status == 'running') {
                timeOutIdentifier = setTimeout(function () {
                    $scope.summary.$get({uuid: tide.uuid})['finally'](function() {
                        reloadSummaryIfRunning();
                    });
                }, 4000);
            }
        };

        $scope.$on('$destroy', function() {
            timeOutIdentifier && clearTimeout(timeOutIdentifier);
        });

        reloadSummaryIfRunning();
    });
