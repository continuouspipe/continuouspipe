'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function(TideRepository, TideSummaryRepository, $scope, $state, tide, summary) {
        $scope.tide = tide;
        $scope.summary = summary;

        var timeOutIdentifier = null,
            reloadSummaryIfNotCompleted = function() {
            if (['running', 'pending'].indexOf($scope.summary.status) != -1) {
                timeOutIdentifier = setTimeout(function () {
                    $scope.summary.$get({uuid: tide.uuid})['finally'](function() {
                        reloadSummaryIfNotCompleted();
                    });
                }, 4000);
            }
        };

        $scope.$on('$destroy', function() {
            timeOutIdentifier && clearTimeout(timeOutIdentifier);
        });

        reloadSummaryIfNotCompleted();

        $scope.cancel = function() {
            $scope.isLoading = true;
            TideRepository.cancel(tide).then(function() {}, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while cancelling the tide";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        TideSummaryRepository.findExternalRelations(tide).then(function(relations) {
            $scope.relations = relations;
        });
    });
