'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowCreateController', function($scope, $remoteResource, $state, FlowRepository, RepositoryRepository, OrganisationRepository) {
        $remoteResource.load('organisations', OrganisationRepository.findAll()).then(function (organisations) {
            $scope.organisations = organisations;
        });

        var loadRepositoryList = function(repositories) {
            $scope.select(undefined);

            $remoteResource.load('repositories', repositories).then(function (repositories) {
                $scope.repositories = repositories;
            });
        };

        $scope.selectPersonal = function() {
            $scope.selectedSource = '';
            loadRepositoryList(RepositoryRepository.findForCurrentUser());
        };

        $scope.selectOrganisation = function(organisation) {
            $scope.selectedSource = organisation;
            loadRepositoryList(RepositoryRepository.findByOrganisation(organisation));
        };

        $scope.select = function(repository) {
            $scope.selectedRepository = repository;
        };

        $scope.selectPersonal();

        $scope.create = function() {
            FlowRepository.createFromRepositoryAndTasks($scope.selectedRepository, $scope.selectedTasks).then(function(flow) {
                $state.go('flow', {uuid: flow.uuid});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating flow";
                swal("Error !", message, "error");
            });
        };

        $scope.availableTasks = [
            {name: 'build', description: 'Build Docker images found in your `docker-compose.yml` file.'},
            {name: 'run', description: 'Verify that a sequence of commands succeeds on the container.', context: {}},
            {name: 'deploy', description: 'Deploy the environment to a given Cloud Provider.', context: {}}
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
