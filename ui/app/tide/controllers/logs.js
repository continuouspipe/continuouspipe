'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function(TideRepository, TideSummaryRepository, LogFinder, $scope, $state, $http, flow, tide, summary) {
        $scope.tide = tide;
        $scope.summary = summary;
        $scope.log = LogFinder.find(tide.log_id);

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
                swal("Error !", $http.getError(error) || "An unknown error occured while cancelling the tide", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.retry = function() {
            $scope.isLoading = true;

            TideRepository.create(flow, {
                branch: tide.code_reference.branch,
                sha1: tide.code_reference.sha1
            }).then(function(created) {
                $state.go('tide.logs', {
                    uuid: flow.uuid,
                    tideUuid: created.uuid
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating the tide", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        TideSummaryRepository.findExternalRelations(tide).then(function(relations) {
            $scope.relations = relations;
        });
    });
