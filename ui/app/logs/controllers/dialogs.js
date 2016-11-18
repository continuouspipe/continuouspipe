'use strict';

angular.module('continuousPipeRiver')
	.controller('LogsDialogController', function($scope, $mdDialog) {
	    $scope.close = function() {
	    	$mdDialog.cancel();
	    };
	})
    .controller('LogsComponentDialogController', function($scope, $mdDialog) {
        $scope.close = function() {
            $mdDialog.cancel();
        };

        if ($scope.component.status.containers.length == 1) {
            $scope.selectedPod = $scope.component.status.containers[0];
        }
    });
