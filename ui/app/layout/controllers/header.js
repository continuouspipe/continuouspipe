'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function ($rootScope, $scope, $state, $intercom, RIVER_API_URL, user, $tokenStorage) {
        $rootScope.user = $scope.user = user;

        $scope.redirectToLogout = function () {
            $intercom.trackEvent('logged-out', {});

            $tokenStorage.remove();

            window.location.href = RIVER_API_URL + '/auth/logout';
        };

        $scope.redirectToAccount = function () {
            $intercom.trackEvent('opened-account', {});

            $state.go('connected-accounts');
        };
    });
