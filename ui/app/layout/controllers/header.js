'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function ($rootScope, $scope, $state, AUTHENTICATOR_API_URL, user, $tokenStorage) {
        $rootScope.user = $scope.user = user;

        $scope.redirectToLogout = function () {
            Intercom('trackEvent', 'logged-out', {});

            $tokenStorage.remove();

            window.location.href = AUTHENTICATOR_API_URL + '/logout';
        };

        $scope.redirectToAccount = function () {
            Intercom('trackEvent', 'opened-account', {});

            $state.go('connected-accounts');
        };
    });
