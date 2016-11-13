'use strict';

angular.module('continuousPipeRiver')
	.controller('LogsPodController', function($scope, $mdDialog, $remoteResource, $http, $flowContext, LogFinder, RIVER_API_URL) {
        $scope.reload = function() {
            $scope.timedOut = false;
            $remoteResource.load('log', $http.post(RIVER_API_URL+'/flows/'+$flowContext.getCurrentFlow().uuid+'/environments/watch', {
                'cluster': $scope.environment.cluster,
                'environment': $scope.environment.identifier,
                'pod': $scope.pod.name
            })).then(function(response) {
                $scope.log = LogFinder.find(response.data.identifier);

                $scope.log.$watch(function(event) {
                    $scope.timedOut = !$scope.log.children;
                });
            });
        };

	    $scope.close = function() {
	    	$mdDialog.cancel();
	    };

        $scope.reload();
	});
