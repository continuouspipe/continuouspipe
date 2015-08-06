'use strict';

angular.module('continuousPipeRiver')
    .controller('TideLogsController', function($scope, tide, flow) {
        $scope.tide = tide;
        $scope.flow = flow;
    });
