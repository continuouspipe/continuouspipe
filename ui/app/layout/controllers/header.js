'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, AUTHENTICATOR_API_URL, user) {
        $scope.user = user;
        $scope.redirectToLogout = function() {
    		window.location.href = AUTHENTICATOR_API_URL+'/logout';
        };
    });
