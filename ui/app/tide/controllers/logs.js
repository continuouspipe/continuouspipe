'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function(TideRepository, EnvironmentRepository, TideSummaryRepository, LogFinder, EndpointOpener, $scope, $state, $http, flow, tide, summary, user, project, UserPermissionsRepository) {
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

        var getCurrentUserPermissions = function() {
            UserPermissionsRepository.findForUserAndProject(user, project).then(function(permissions) {
                $scope.isAdmin =  permissions.indexOf('ADMIN') > -1;
            });
        };

        $scope.$on('$destroy', function() {
            timeOutIdentifier && clearTimeout(timeOutIdentifier);
        });

        reloadSummaryIfNotCompleted();
        getCurrentUserPermissions();

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
                if (created.length > 1) {
                    $state.go('flow.dashboard', {
                        uuid: flow.uuid
                    });
                } else if (created.length == 1) {
                    $state.go('tide.logs', {
                        uuid: flow.uuid,
                        tideUuid: created[0].uuid
                    });
                } else {
                    swal("No tide created", "No tide was created as the result of your request. Maybe no pipeline is matching?", "error");
                }
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating the tide", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.deleteAndRetry = function(environment) {
            swal({
                title: "Are you sure?",
                text: "The environment "+environment.identifier+" won't be recoverable",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, remove it!",
                closeOnConfirm: true
            }, function() {
                EnvironmentRepository.delete(flow, environment).then(function () {
                    $scope.retry();

                }, function (error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the environment", "error");
                });
            });
        };

        TideSummaryRepository.findExternalRelations(tide).then(function(relations) {
            $scope.relations = relations;
        });

        $scope.openEndpoint = function(endpoint) {
            return EndpointOpener.open(endpoint);
        };
    });
