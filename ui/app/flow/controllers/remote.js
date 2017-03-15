'use strict';

angular.module('continuousPipeRiver')
    .controller('ListOfDevelopmentEnvironmentsController', function($remoteResource, $scope, RemoteRepository, flow) {
        $remoteResource.load('environments', RemoteRepository.findByFlow(flow)).then(function (environments) {
            $scope.environments = environments;
        });
    })
    .controller('DevelopmentEnvironmentController', function ($scope, $state, $mdToast, $stateParams, RemoteRepository, EndpointOpener, $http, TideRepository, flow, user, developmentEnvironmentStatus) {
        $scope.flow = flow;

        var refresh = function(status) {
            $scope.developmentEnvironmentStatus = $.extend(true, $scope.developmentEnvironmentStatus, status);
            $scope.hasBeenCreated = ['TokenNotCreated', 'NotStarted'].indexOf(status.status) == -1;

            // Do not refresh until we know that the environment has been successfully
            // initialized with the token it created. Until this is fixed with the UI, we allow to refresh
            // the screen with a "Refresh" button.
            //
            // if (!$scope.hasBeenCreated) {
            //     setTimeout(function() {
            //         RemoteRepository.getStatus(flow, status.development_environment.uuid).then(function(status) {
            //             refresh(status);
            //         });
            //     }, 5000);
            // }

            $scope.refresh = function() {
                RemoteRepository.getStatus(flow, status.development_environment.uuid).then(function(status) {
                    refresh(status);
                });
            };
        };

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

        // When the environment is already created
        $scope.openEndpoint = function(endpoint) {
            return EndpointOpener.open(endpoint);
        };

        $scope.delete = function() {
            swal({
                title: "Are you sure?",
                text: "After deleting the environment you'll have to recreate and reconfigure your development environment.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function() {
                RemoteRepository.delete(flow, developmentEnvironmentStatus.development_environment).then(function() {
                    $state.go('flow.development-environments');

                    swal("Deleted!", "Development environment successfully deleted.", "success");
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the environment", "error");
                })['finally'](function() {
                    $scope.isLoading = false;
                });
            });
        };

        $scope.rebuild = function() {
            $scope.isLoading = true;
            TideRepository.create(flow, {branch: developmentEnvironmentStatus.last_tide.code_reference.branch}).then(function() {
                $mdToast.show($mdToast.simple()
                    .textContent('The tide has been created!')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content#content'))
                );
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating a tide", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        refresh(developmentEnvironmentStatus);
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
        };
    })
;
