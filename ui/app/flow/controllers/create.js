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
            FlowRepository.createFromRepositoryAndTasks($scope.selectedRepository, $scope.selectedTasks).then(function(flow) {
                $state.go('flow', {uuid: flow.uuid});
            }, function() {
                swal("Error !", "An unknown error occured while creating flow", "error");
            });
        };

        $scope.availableTasks = [
            {name: 'build', description: 'Build Docker images found in your `docker-compose.yml` file.'},
            {name: 'deploy', description: 'Deploy the environment to a given Cloud Provider.', context: {
                providerName: null
            }}
        ];
        $scope.selectedTasks = [];

        $scope.addTask = function(index) {
            $scope.selectedTasks.push($scope.availableTasks[index]);
            $scope.availableTasks.splice(index, 1);
        };

        $scope.removeTask = function(index) {
            $scope.availableTasks.push($scope.selectedTasks[index]);
            $scope.selectedTasks.splice(index, 1);
        };
    });
