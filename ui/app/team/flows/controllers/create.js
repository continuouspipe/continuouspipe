'use strict';

angular.module('continuousPipeRiver')
    .controller('CreateFlowController', function($scope, $state, $remoteResource, $http, AUTHENTICATOR_API_URL, WizardRepository, AccountRepository, RegistryCredentialsRepository, ClusterRepository, FlowRepository, team, user) {
        $scope.linkAccountUrl = AUTHENTICATOR_API_URL + '/account/';
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
            }

            $scope.wizard = {
                organisation: null
            };

            $scope.organisations = [];
            $remoteResource.load('organisations', WizardRepository.findOrganisations(account)).then(function (organisations) {
                $scope.organisations = organisations;
            });

            $scope.repositories = [];
            loadRepositoryList(WizardRepository.findRepositoryByCurrentUser($scope.account));
        });

        var loadRepositoryList = function(repositories) {
            $scope.repositories = [];
            $remoteResource.load('repositories', repositories).then(function (repositories) {
                $scope.repositories = repositories;
            });
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
