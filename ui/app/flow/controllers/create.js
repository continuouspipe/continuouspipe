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
            FlowRepository.createFromRepository($scope.selectedRepository).then(function(flow) {
                $state.go('flow.configuration', {uuid: flow.uuid});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating flow";
                swal("Error !", message, "error");
            });
        };
    });
