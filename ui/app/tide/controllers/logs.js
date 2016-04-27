'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function(TideRepository, $scope, $state, tide, summary) {
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

        $scope.cancel = function() {
            $scope.isLoading = true;
            TideRepository.cancel(tide).then(function() {}, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while cancelling the tide";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
