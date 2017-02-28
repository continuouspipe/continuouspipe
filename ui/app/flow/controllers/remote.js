'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowRemoteController', function ($scope, $remoteResource, RemoteRepository, flow) {
        $scope.environments = [];

        RemoteRepository.getDevEnvironments(flow).then(function (environemnts) {
            environemnts.forEach(function (env) {
                console.log(env);
                $scope.environments.push(env);
            });
        });

        $scope.getToken = function() {

        };
    })
;
