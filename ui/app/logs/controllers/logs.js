'use strict';

angular.module('continuousPipeRiver')
    .controller('LogsPodsController', function($scope, $mdDialog, $flowContext, $intercom) {
        $scope.isNewGeneration = function(deployment, pod) {
            return deployment.containers[0].image == pod.containers[0].image;
        };

        $scope.getPodClasses = function(deployment, pod) {
            var classes = [];
            if (!pod.status) {
                return classes;
            }

            // Add the pod status labels
            if (pod.deletionTimestamp) {
                classes.push('pod-terminating');
            } else if (pod.status.phase == 'Pending') {
                classes.push('pod-pending');
            } else if (pod.status.phase == 'Running') {
                if (pod.status.ready) {
                    classes.push('pod-ready');
                } else {
                    classes.push('pod-running');
                }
            } else if (pod.status.phase == 'Failed') {
                classes.push('pod-failed');
            } else {
                classes.push('pod-unknown');
            }

            return classes;
        };

        $scope.liveStreamPod = function(deployment, pod) {
            var dialogScope = $scope.$new();
            dialogScope.pod = pod;
            dialogScope.environment = deployment.environment;

            $mdDialog.show({
                controller: 'LogsDialogController',
                templateUrl: 'logs/views/dialogs/pod.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                fullscreen: true,
                scope: dialogScope
            });

            $intercom.trackEvent('streamed-pod-log', {
                environment: deployment.environment,
                pod: pod,
                flow: $flowContext.getCurrentFlow().uuid,
                source: 'pods-log'
            });
        };
    })
    .controller('LogsManualApprovalController', function($scope, $resource, RIVER_API_URL) {
        var resource = $resource(RIVER_API_URL+'/tides/:uuid/tasks/:task/:choice'),
            doChoice = function(log, choice) {
                $scope.isLoading = true;
                resource.save({uuid: log.tide_uuid, task: log.task_identifier, choice: choice}, {}).$promise.then(function() {}, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occured while deleting the environment", "error");
                })['finally'](function() {
                    $scope.isLoading = false;
                });
            };

        $scope.approve = function(log) {
            doChoice(log, 'approve');
        };

        $scope.reject = function(log) {
            swal({
                title: "Are you sure?",
                text: "The tide will be rejected and the following tasks won\'t be run.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, reject it!"
            }).then(function() {
                doChoice(log, 'reject');
            }).catch(swal.noop);
        };
    });
