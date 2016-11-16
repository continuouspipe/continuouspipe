'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowEnvironmentsController', function($scope, $remoteResource, $http, $mdDialog, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        var getEnvironmentStatus = function(environment) {
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
                    environment.status = getEnvironmentStatus(environment);
                    environment.endpoints = getEnvironmentEndpoints(environment);

                    return environment;
                });
            });
        };

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
                    swal("Error !", $http.getError(error) || "An unknown error occured while deleting the environment", "error");
                });
            });
        };

        loadEnvironments();

        $scope.openEndpoint = function(endpoint) {
            var confirm = $mdDialog.confirm()
              .title('This will open the endpoint "'+endpoint.name+'"')
              .textContent('The address you will be redirected to ('+endpoint.address+') is outside of ContinuousPipe and will work on your browser only it is answers to HTTP.')
              .ok('Open it!')
              .cancel('Cancel');

            $mdDialog.show(confirm).then(function() {
                window.open('http://'+endpoint.address, '_blank');
            });
        };

        $scope.liveStreamComponent = function(environment, component) {
            var dialogScope = $scope.$new();
            dialogScope.environment = environment;
            dialogScope.component = component;

            $mdDialog.show({
                controller: 'LogsDialogController',
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
    });
