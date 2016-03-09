'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function($scope, $state, tide, summary) {
        $scope.tide = tide;
        $scope.summary = summary;

        var reloadSummaryIfRunning = function() {
            if ($scope.summary.status == 'running') {
                setTimeout(function () {
                    $scope.summary.$get({uuid: tide.uuid})['finally'](function() {
                        reloadSummaryIfRunning();
                    });
                }, 4000);
            }
        };

        reloadSummaryIfRunning();
    });
