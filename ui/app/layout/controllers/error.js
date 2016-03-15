'use strict';

angular.module('continuousPipeRiver')
    .controller('ErrorController', function($scope, $errorContext) {
    	$scope.error = $errorContext.get();
        $scope.message = (($scope.error || {}).data || {}).message;
    });
