'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, user) {
        $scope.user = user;
    });
