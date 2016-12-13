'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($scope, $remoteResource, $mdToast, $state, $http, TideRepository, EnvironmentRepository, FlowRepository, flow) {
        $scope.flow = flow;
        $scope.variables = [];

        var aceInitialized = false,
            changed = false;

        $scope.aceOption = {
            mode: 'yaml',
            onBlur: loadVariables,
            onChange: function() {
                if (aceInitialized) {
                    changed = true;
                } else {
                    aceInitialized = true;
                }
            }
        };

        $scope.save = function() {
            $scope.isLoading = true;

            FlowRepository.updateConfiguration(flow).then(function() {
                $mdToast.show($mdToast.simple()
                    .textContent('Configuration successfully saved!')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content.configuration-content'))
                );

                Intercom('trackEvent', 'updated-configuration', {
                    flow: flow.uuid
                });

                loadVariables();
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating flow", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $remoteResource.load('configuration', FlowRepository.getConfiguration(flow)).then(function(configuration) {
            $scope.configuration = configuration;
            $scope.flow.yml_configuration = jsyaml.safeDump(configuration.configuration);
            $scope.missing_variables = configuration.missing_variables;

            loadVariables();
        });

        var loadVariables = function() {
            if (!$scope.flow.yml_configuration) {
                return;
            }

            var parsed = loadYamlConfiguration();
            if (parsed.environment_variables || parsed.variables) {
                $scope.variables = parsed.environment_variables || parsed.variables;
            } else {
                $scope.variables = [];
            }

            addMissingVariables();
        };

        var addMissingVariables = function() {
            var foundVariables = $scope.variables.map(function(variable) {
                return variable.name;
            });

            for (var i = 0; i < $scope.missing_variables.length; i++) {
                var variable = $scope.missing_variables[i];

                if (foundVariables.indexOf(variable) !== -1) {
                    continue;
                }

                $scope.addVariable(variable);
            }
        };

        var loadYamlConfiguration = function() {
            var configuration = jsyaml.load($scope.flow.yml_configuration);

            // If the loaded configuration was considered as an array, reduce it
            // to an object.
            if (configuration.reduce) {
                configuration = configuration.reduce(function(o, v, i) {
                    o[i] = v;

                    return o;
                }, {});
            }

            return configuration;
        };

        $scope.$watch('variables', function(variables) {
            if (!variables || !$scope.flow.yml_configuration) {
                return;
            }

            var parsed = loadYamlConfiguration();
            var target = parsed.environment_variables ? 'environment_variables' : 'variables';

            parsed[target] = 
                variables.filter(function(variable) {
                    return variable.name && variable.value;
                }).map(function(variable) {
                    var yamlVariable = {
                        name: variable.name,
                        value: variable.value
                    };

                    if (variable.condition) {
                        yamlVariable.condition = variable.condition;
                    }

                    return yamlVariable;
                });

            $scope.flow.yml_configuration = jsyaml.dump(parsed);
        }, true);

        $scope.addVariable = function(name) {
            $scope.variables.push({
                name: name || '',
                value: ''
            });
        };

        $scope.removeVariableByKey = function(key) {
            $scope.variables.splice(key, 1);
        };

        $scope.delete = function() {
            swal({
                title: "Are you sure?",
                text: "This will remove the flow and its tide history",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                FlowRepository.remove(flow).then(function() {
                    swal("Deleted!", "Flow successfully deleted.", "success");

                    $state.go('flows');

                    Intercom('trackEvent', 'deleted-flow', {
                        flow: flow.uuid
                    });
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the flow", "error");
                });
            });
        };
    });
