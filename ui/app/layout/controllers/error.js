'use strict';

angular.module('continuousPipeRiver')
    .controller('ErrorController', function($scope, $errorContext, $http) {
    	$scope.error = $errorContext.get();
        $scope.message = $http.getError($scope.error);
    });
