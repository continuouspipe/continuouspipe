'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowCreateController', function($scope, $remoteResource, $state, FlowRepository, RepositoryRepository, OrganizationRepository) {
        $remoteResource.load('repositorySources', OrganizationRepository.findAll()).then(function (organizations) {
            organizations.unshift({
                'organization': {
                    'login': 'Personal repositories',
                    'personal': true,
                    'active': true
                }
            });

            $scope.repositorySources = organizations;
        });

        var loadRepositoryList = function(repositories) {
            $remoteResource.load('repositories', repositories).then(function (repositories) {
                console.log(repositories);
                $scope.repositories = repositories;
            });
        };

        $scope.switchRepositorySource = function(repositorySource) {
            if (repositorySource.personal == true) {
                loadRepositoryList(RepositoryRepository.findForCurrentUser());
            } else {
                loadRepositoryList(RepositoryRepository.findByOrganization(repositorySource.login));
            }
        };

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
