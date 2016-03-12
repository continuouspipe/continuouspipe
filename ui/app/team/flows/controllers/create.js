'use strict';

angular.module('continuousPipeRiver')
    .controller('CreateFlowController', function($scope, $remoteResource, WizardRepository, user) {
        $scope.user = user;
        $scope.wizard = {
            step: 0
        };
        $scope.selectedRepository = {
            organisation: null
        };

        // 1. Select the repository
        $remoteResource.load('organisations', WizardRepository.findOrganisations()).then(function (organisations) {
            $scope.organisations = organisations;
        });

        var loadRepositoryList = function(repositories) {
            $remoteResource.load('repositories', repositories).then(function (repositories) {
                $scope.repositories = repositories;
            });
        };

        $scope.$watch('selectedRepository.organisation', function(organisation) {
            if (!organisation) {
                loadRepositoryList(WizardRepository.findRepositoryByCurrentUser());
            } else {
                loadRepositoryList(WizardRepository.findRepositoryByOrganisation(organisation));
            }
        });

        // 2. Images to build
        $scope.$watchGroup(['selectedRepository.repository', 'selectedRepository.branch'], function(updatedValues) {
            var repository = updatedValues[0],
                branch = updatedValues[1];

            if (!repository) {
                return;
            }

            if (!branch) {
                $scope.selectedRepository.branch = repository.repository.default_branch;
            } else {
                $remoteResource.load('components', WizardRepository.findComponentsByRepositoryAndBranch(repository, branch)).then(function (components) {
                    $scope.components = components;
                });
            }
        });
    });
