'use strict';

angular.module('logstream')
    .controller('LogsCtrl', function ($routeParams, $firebaseObject, $scope) {
        var root = new Firebase('https://continuous-pipe.firebaseio.com/logs');

        $scope.root = $firebaseObject(root.child($routeParams.identifier));
    });
