'use strict';

angular.module('continuousPipeRiver')
    .controller('BranchesController', function ($scope, $http, $mdToast, $firebaseArray, $authenticatedFirebaseDatabase, PinnedBranchRepository, flow, user, project, BranchFactory, $mdDialog, $remoteResource, EnvironmentRepository, $rootScope) {
        $scope.isAdmin = user.isAdmin(project);
        
        $authenticatedFirebaseDatabase.get(flow).then(function (database) {
            $scope.branches = BranchFactory(
                database.ref().child('flows/' + flow.uuid + '/branches')
            );

            var pullRequestsByBranch = $firebaseArray(
                database.ref().child('flows/' + flow.uuid + '/pull-requests/by-branch')
            );


            var reloadPullRequests = function() {
                $scope.pullRequests = pullRequestsByBranch
                    .filter(function(pullRequestInBranch) {
                        return $scope.branches.some(function(branch) {
                            return branch.$id == pullRequestInBranch.$id
                        });
                    })
                    .map(function(pullRequestInBranch) {
                        var view = $scope.branches.filter(function(branch) {return branch.$id == pullRequestInBranch.$id})[0];
                        view.pull_request = pullRequestInBranch;

                        return view;
                    })
                ;
            };

            $scope.branches.$loaded(reloadPullRequests);
            pullRequestsByBranch.$watch(reloadPullRequests);
        });

        $scope.pinOrUnPin = function(branch) {
            var method = branch.data.pinned ? 'unpin' : 'pin';
            PinnedBranchRepository[method](flow.uuid, branch.data.name).then(function(response) {
                $mdToast.show($mdToast.simple()
                    .textContent('Branch successfully '+method+'ned')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('#content')));
            }, function(response) {
                swal("Error !", $http.getError(response), "error");
            })
        };
        
        $scope.hasEnvironment = function(environment) {
            var envs = $scope.environments.filter(function (env) {
                return environment.identifier == env.identifier;
            });

            return envs.length > 0;
        };
        
        $scope.showAlert = function(ev, environment) {

            var envs = $scope.environments.filter(function (env) {
                return environment.identifier == env.identifier;
            });
            
            if (envs.length) {
                environment = envs[0];
            }

            var mdDialogCtrl = function ($scope, EndpointOpener, RemoteShellOpener) {

                $scope.environment = environment;
                $scope.flow = flow;
                $scope.openEndpoint = function(endpoint) {
                    EndpointOpener.open(endpoint);
                };

                $scope.openRemoteShell = function(environment, endpoint) {
                    RemoteShellOpener.open(environment, endpoint)
                };

                $scope.liveStreamComponent = function(environment, component) {
                    var liveStreamCtrl = function ($scope) {
                        $scope.environment = environment;
                        $scope.component = component;

                        $scope.close = function() {
                            $mdDialog.cancel();
                        };

                        if ($scope.component.status.containers.length === 1) {
                            $scope.selectedPod = $scope.component.status.containers[0];
                        }
                    };


                    var dialogScope = $scope.$new();
                    dialogScope.flow = flow;
                    dialogScope.environment = environment;
                    dialogScope.component = component;

                    $mdDialog.show({
                        controller: liveStreamCtrl,
                        templateUrl: 'logs/views/dialogs/components.html',
                        clickOutsideToClose: true,
                    });
                };

            };

            $mdDialog.show({
                templateUrl: 'flow/views/branches/environment.html',
                controller: mdDialogCtrl,
                clickOutsideToClose: true
            });
        };

        $scope.environments = [];

        var loadEnvironments = function() {
            $remoteResource.load('environments', EnvironmentRepository.findByFlow(flow)).then(function (environments) {
                $scope.environments = environments.map(function(environment) {
                    environment.status = getEnvironmentStatus(environment);
                    environment.endpoints = getEnvironmentEndpoints(environment);
                    environment.flow = flow;

                    return environment;
                });
                
            });
        };

        var getEnvironmentStatus = function(environment) {
            if (environment.status == 'Terminating') {
                return 'terminating';
            }

            var status = 'healthy';

            for (var i = 0; i < environment.components.length; i++) {
                var component = environment.components[i];

                if (component.status.status != 'healthy') {
                    status = component.status.status;
                }
            }

            return status;
        };

        var getEnvironmentEndpoints = function(environment) {
            var endpoints = [];

            environment.components.forEach(function(component) {
                component.status.public_endpoints.forEach(function(endpoint) {
                    endpoints.push({
                        name: component.name,
                        address: endpoint
                    });
                })
            });

            return endpoints;
        };

        loadEnvironments();
    });
