'use strict';

angular.module('continuousPipeRiver')
    .controller('CreateFlowController', function($scope, $state, $remoteResource, $http, WizardRepository, AccountRepository, RegistryCredentialsRepository, ClusterRepository, FlowRepository, project, user) {
        $scope.user = user;

        $remoteResource.load('accounts', AccountRepository.findMine()).then(function(accounts) {
            return accounts.filter(function(account) {
                return account.type == 'github' || account.type == 'bitbucket';
            });
        }).then(function(accounts) {
            $scope.accounts = accounts;

            if ($scope.accounts.length == 1) {
                $scope.account = $scope.accounts[0];
            }
        });

        $scope.$watch('account', function(account) {
            if (!account) {
                return;
            } else if (account === 'add') {
                $state.go('connected-accounts');
                
                return;
            }

            $scope.wizard = {
                organisation: null
            };

            $scope.organisations = null;
            $remoteResource.load('organisations', WizardRepository.findOrganisations(account)).then(function (organisations) {
                $scope.organisations = organisations;
            });

            loadRepositoryList(WizardRepository.findRepositoryByCurrentUser($scope.account));
        });

        var currentRepositoriesPromise;
        var loadRepositoryList = function(repositoriesPromise) {            
            $scope.repositories = null;
            $remoteResource.load('repositories', repositoriesPromise).then(function (repositories) {
                $scope.repositories = repositories;
            });

            if (currentRepositoriesPromise && currentRepositoriesPromise.cancel) {
                currentRepositoriesPromise.cancel();
            }

            currentRepositoriesPromise = repositoriesPromise;
        };

        $scope.$watch('wizard.organisation', function(organisation) {
            if (!$scope.account) {
                return;
            }

            if (!organisation) {
                loadRepositoryList(WizardRepository.findRepositoryByCurrentUser($scope.account));
            } else {
                loadRepositoryList(WizardRepository.findRepositoryByOrganisation($scope.account, organisation));
            }
        });

        // 6. Finish that sh*t
        $scope.create = function() {
            $scope.isLoading = true;

            FlowRepository.createFromRepositoryAndProject(project, $scope.wizard.repository).then(function(flow) {
                $state.go('flow.configuration.checklist', {uuid: flow.uuid});

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
