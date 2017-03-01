'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowRemoteController', function ($scope, $remoteResource, RemoteRepository, flow) {
        $scope.environments = [];
        $scope.branchName = 'test';
        $scope.token = '';

        RemoteRepository.getDevEnvironments(flow).then(function (environments) {
            environments.forEach(function (env) {
                $scope.environments.push(env);
            });
        });

        $scope.getToken = function (branchName, flow) {
            RemoteRepository.issueToken(branchName, flow).then(function (token) {
                $scope.token = token;
                console.log($scope.token);
            });
        };

        $scope.createEnvironment = function (name, flow) {
            RemoteRepository.createDevEnvironment(name, flow).then(function (env) {
                $scope.environments.push(env);
            });
        };

        $scope.delete = function (environment) {
        };
    })
;
