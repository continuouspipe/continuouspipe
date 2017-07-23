'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowEnvironmentsController', function($scope, $remoteResource, $http, $mdDialog, $componentLogDialog, TideRepository, EnvironmentRepository, EndpointOpener, RemoteShellOpener, flow, user, project) {
        $scope.flow = flow;

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

        var loadEnvironments = function() {
            $remoteResource.load('environments', EnvironmentRepository.findByFlow(flow)).then(function (environments) {
                $scope.environments = environments.map(function(environment) {
                    var flowUuidPrefix = flow.uuid+'-';
                    
                    // Remove the flow UUID prefix if it exists
                    environment.displayName = environment.identifier.indexOf(flowUuidPrefix) === 0 ?
                        environment.identifier.substr(flowUuidPrefix.length) :
                        environment.identifier;

                    environment.status = getEnvironmentStatus(environment);
                    environment.endpoints = getEnvironmentEndpoints(environment);
                    environment.flow = flow;

                    return environment;
                });
            });
        };

        $scope.isAdmin = user.isAdmin(project);

        $scope.delete = function(environment) {
            swal({
                title: "Are you sure?",
                text: "The environment "+environment.identifier+" won't be recoverable",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, remove it!",
                closeOnConfirm: false
            }, function() {
                EnvironmentRepository.delete(flow, environment).then(function () {
                    swal("Deleted!", "Environment successfully deleted.", "success");

                    loadEnvironments();
                }, function (error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the environment", "error");
                });
            });
        };

        loadEnvironments();

        $scope.openEndpoint = function(endpoint) {
            EndpointOpener.open(endpoint);
        };

        $scope.openRemoteShell = function(environment, endpoint) {
            RemoteShellOpener.open(environment, endpoint)
        };

        $scope.liveStreamComponent = function(environment, component) {
            $componentLogDialog.open($scope, flow, environment, component);
        };
    })
    .controller('EnvironmentPreviewController', function($rootScope, $scope, $componentLogDialog, EndpointOpener, environment, flow, $sce) {
        $scope.environment = environment;
        $scope.pointer = true;

        environment.components.forEach(function(component) {
            if (component.status.public_endpoints.length > 0) {
                $scope.url = $sce.trustAsResourceUrl('https://' + component.status.public_endpoints[0]);
            }
        });

        $scope.openEndpoint = function(endpoint) {
            EndpointOpener.open(endpoint);
        };

        $scope.getEnvironmentEndpoints = function(environment) {
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

        $scope.$on("angular-resizable.resizeStart", function(e, a) {
            $scope.pointer = false;
        });

        $scope.$on("angular-resizable.resizeEnd", function(e, a) {
            $scope.pointer = true;
        });

        $scope.liveStreamComponent = function(environment, component) {
            $rootScope.$emit('openComponentLogs', component);
        };

        $rootScope.$on('openComponentLogs', function(event, component) {
            $scope.component = component;
        });

        $rootScope.$on('closeComponentLogs', function() {
            $scope.component = null;
        });
    })
    .directive('componentsLogs', function() {
        return {
            restrict: 'E',
            scope: {
                environment: '=',
                component: '='
            },
            controller: 'LogsComponentDialogController',
            templateUrl: 'flow/views/environments/inline/component.html'
        }
    })
    .service('$componentLogDialog', function($mdDialog) {
        this.open = function($scope, flow, environment, component) {
            var dialogScope = $scope.$new();
            dialogScope.environment = environment;
            dialogScope.component = component;

            $mdDialog.show({
                controller: 'LogsComponentDialogController',
                templateUrl: 'logs/views/dialogs/components.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                scope: dialogScope
            });

            Intercom('trackEvent', 'streamed-component-log', {
                environment: environment,
                component: component,
                flow: flow.uuid,
                source: 'environment-list'
            });
        };
    })
;
