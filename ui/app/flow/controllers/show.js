'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowController', function($scope, flow) {
        $scope.flow = flow;
    });
