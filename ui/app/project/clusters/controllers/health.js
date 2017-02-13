'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamClusterHealthController', function($scope, $remoteResource, $mdDialog, ClusterRepository) {
        $scope.close = function() {
            $mdDialog.cancel();
        };

        $remoteResource.load('problems', ClusterRepository.findProblems($scope.team, $scope.cluster)).then(function (problems) {
            $scope.problems = problems;
        });
    });
