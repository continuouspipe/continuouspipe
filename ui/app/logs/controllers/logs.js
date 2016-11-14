'use strict';

angular.module('continuousPipeRiver')
    .controller('LogsPodsController', function($scope, $mdDialog, $flowContext) {
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
                controller: 'LogsPodController',
                templateUrl: 'logs/views/pod/logs.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                fullscreen: true,
                scope: dialogScope
            });

            Intercom('trackEvent', 'streamed-pod-log', {
                environment: deployment.environment,
                pod: pod,
                flow: $flowContext.getCurrentFlow().uuid,
                source: 'pods-log'
            });
        };
    });
