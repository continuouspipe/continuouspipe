'use strict';

angular.module('continuousPipeRiver')
    .controller('CreateFlowController', function($scope, $state, $remoteResource, $http, WizardRepository, RegistryCredentialsRepository, ClusterRepository, FlowRepository, team, user) {
        $scope.user = user;
        $scope.wizard = {
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

        $scope.$watch('wizard.organisation', function(organisation) {
            if (!organisation) {
                loadRepositoryList(WizardRepository.findRepositoryByCurrentUser());
            } else {
                loadRepositoryList(WizardRepository.findRepositoryByOrganisation(organisation));
            }
        });

        // 6. Finish that sh*t
        $scope.create = function() {
            $scope.isLoading = true;

            FlowRepository.createFromRepositoryAndTeam(team, $scope.wizard.repository).then(function(flow) {
                $state.go('flow.tides', {uuid: flow.uuid});

                Intercom('trackEvent', 'created-flow', {
                    flow: flow
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating the flow", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
