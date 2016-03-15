'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowEnvironmentsController', function($scope, $remoteResource, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        $remoteResource.load('environments', EnvironmentRepository.findByFlow(flow)).then(function (environments) {
            $scope.environments = environments.map(function(environment) {
                environment.name = environment.name.substr(flow.uuid.length + 1);

                return environment;
            });
        });
    });
