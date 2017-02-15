'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectClusterHealthController', function($scope, $remoteResource, $mdDialog, ClusterRepository) {
        $scope.close = function() {
            $mdDialog.cancel();
        };

        $remoteResource.load('problems', ClusterRepository.findProblems($scope.project, $scope.cluster)).then(function (problems) {
            $scope.problems = problems;
        });
    });
