'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, $userContext, AUTHENTICATOR_API_URL) {
        $scope.authenticatorAccountUrl = AUTHENTICATOR_API_URL+'/account';
        $scope.user = $userContext.getUser();
    });
