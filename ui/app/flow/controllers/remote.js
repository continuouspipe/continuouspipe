'use strict';

angular.module('continuousPipeRiver')
    .controller('ListOfDevelopmentEnvironmentsController', function($remoteResource, $scope, RemoteRepository, flow) {
        $remoteResource.load('environments', RemoteRepository.findByFlow(flow)).then(function (environments) {
            $scope.environments = environments;
        });
    })
    .controller('DevelopmentEnvironmentController', function ($scope, $mdToast, $stateParams, RemoteRepository, $http, flow, user, developmentEnvironmentStatus) {
        $scope.developmentEnvironmentStatus = developmentEnvironmentStatus;
        $scope.hasBeenCreated = ['TokenNotCreated', 'NotStarted'].indexOf(developmentEnvironmentStatus.status) == -1;

        // Token creation if not has been created
        $scope.tokenRequest = {
            git_branch: 'cpdev/' + user.username
        };

        $scope.getToken = function () {
            $scope.isLoading = true;
            RemoteRepository.issueToken(flow, {uuid: $stateParams.environmentUuid}, $scope.tokenRequest).then(function (token) {
                $scope.token = token.token;
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred creating the initialization token", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    })
    .controller('CreateDevelopmentEnvironmentController', function($scope, $http, $state, RemoteRepository, flow, user) {
        $scope.environment = {
            name: user.username+'\'s environment'
        };

        $scope.create = function(environment) {
            $scope.isLoading = true;
            RemoteRepository.create(flow, environment).then(function(environment) {
                $state.go('flow.development-environment', {
                    environmentUuid: environment.uuid
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred while creating the environment", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        }
    })
;
