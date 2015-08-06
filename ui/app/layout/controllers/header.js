'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, $breadcrumb, AUTHENTICATOR_API_URL) {
        $scope.authenticatorAccountUrl = AUTHENTICATOR_API_URL+'/account';
    });
