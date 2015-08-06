'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowController', function($scope, $remoteResource, TideRepository, flow) {
        $scope.flow = flow;

        $remoteResource.load('tides', TideRepository.findByFlow(flow)).then(function (tides) {
            $scope.tides = tides;
        });
    });
