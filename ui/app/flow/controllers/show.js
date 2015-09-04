'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowController', function($scope, $remoteResource, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        $remoteResource.load('tides', TideRepository.findByFlow(flow)).then(function (tides) {
            $scope.tides = tides;
        });

        $remoteResource.load('environments', EnvironmentRepository.findByFlow(flow)).then(function (environments) {
            $scope.environments = environments.map(function(environment) {
                environment.name = environment.name.substr(flow.uuid.length + 1);

                return environment;
            });
        });
    });
