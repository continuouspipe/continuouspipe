'use strict';

angular.module('continuousPipeRiver')
	.controller('LogsDialogController', function($scope, $mdDialog, $remoteResource, $http, $flowContext, LogFinder, RIVER_API_URL) {
	    $scope.close = function() {
	    	$mdDialog.cancel();
	    };
	});
