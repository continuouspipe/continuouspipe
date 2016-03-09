'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowTidesController', function($scope, $remoteResource, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        $remoteResource.load('tides', TideRepository.findByFlow(flow)).then(function (tides) {
            $scope.tides = tides;
        });
    })
    .controller('FlowCreateTideController', function($scope, $state, TideRepository, flow) {
        $scope.flow = flow;

        $scope.create = function(tide) {
            $scope.isLoading = true;
            $scope.error = null;

            TideRepository.create(flow, tide).then(function() {
                $state.go('flow', {
                    uuid: flow.uuid
                });
            }, function(response) {
                $scope.error = response.data ? response.data.error : 'Unknown error ('+response.status+')';
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
