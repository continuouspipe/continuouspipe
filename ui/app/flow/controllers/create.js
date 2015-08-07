'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowCreateController', function($scope, $remoteResource, $state, FlowRepository, RepositoryRepository) {
        $remoteResource.load('repositories', RepositoryRepository.findAll()).then(function (repositories) {
            $scope.repositories = repositories;
        });

        $scope.select = function(repository) {
            $scope.selectedRepository = repository;
        };

        $scope.finish = function() {
            FlowRepository.createFromRepository($scope.selectedRepository).then(function(flow) {
                $state.go('flow', {uuid: flow.uuid});
            }, function() {
                swal("Error !", "An unknown error occured while creating flow", "error");
            });
        };
    });
