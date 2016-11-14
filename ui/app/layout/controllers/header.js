'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, AUTHENTICATOR_API_URL, user) {
        $scope.user = user;
        $scope.redirectToLogout = function() {
            Intercom('trackEvent', 'logged-out', {});

    		window.location.href = AUTHENTICATOR_API_URL+'/logout';
        };

        $scope.redirectToAccount = function() {
            Intercom('trackEvent', 'opened-account', {});

    		window.location.href = AUTHENTICATOR_API_URL+'/account/';
        };
    });
