'use strict';

angular.module('logstream')
    .controller('LogsCtrl', function ($routeParams, $firebaseObject, $scope) {
        var root = new Firebase('https://continuous-pipe.firebaseio.com/logs');

        $scope.root = $firebaseObject(root.child($routeParams.identifier));
    })
    .controller('LogsPodsController', function($scope) {
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

			// Is this pod a new or old one?
			// pod-new-generation
			// pod-previous-generation
			if (deployment.containers[0].image == pod.containers[0].image) {
				classes.push('pod-new-generation');
			} else {
				classes.push('pod-previous-generation');
			}

			return classes;
    	};
    });
