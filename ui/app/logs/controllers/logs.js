'use strict';

angular.module('continuousPipeRiver')
    .controller('LogsPodsController', function($scope) {
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
    });
