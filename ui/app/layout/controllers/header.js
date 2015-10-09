'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, $userContext, AUTHENTICATOR_API_URL) {
        $scope.logoutUrl = AUTHENTICATOR_API_URL+'/logout';
        $scope.user = $userContext.getUser();
    });
