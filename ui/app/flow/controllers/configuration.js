'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($rootScope, $scope, $remoteResource, $mdToast, $state, $http, TideRepository, EnvironmentRepository, FlowRepository, flow) {
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
                $rootScope.$emit('configuration-saved');

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

            for (var key in $scope.missing_variables) {
                var variable = $scope.missing_variables[key];

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
                    return variable.name && (variable.value || variable.encrypted_value);
                }).map(function(variable) {
                    var yamlVariable = {
                        name: variable.name
                    };

                    if (variable.value) {
                        yamlVariable.value = variable.value;
                    }
                    if (variable.encrypted_value) {
                        yamlVariable.encrypted_value = variable.encrypted_value;
                    }

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

        $scope.encryptByKey = function(key) {
            swal({
                title: "Are you sure?",
                text: "After encrypting the value, you won\'t be able to read the value again on this user interface.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, encrypt it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function() {
                FlowRepository.encrypt(flow, $scope.variables[key]).then(function(encryptedValue) {
                    $scope.variables[key].encrypted_value = encryptedValue;

                    delete $scope.variables[key].value;

                    swal("Variable encrypted!");
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while encrypting the variable", "error");
                });
            });
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
    })
    .controller('FlowConfigurationChecklistController', function($scope, $rootScope, $http, $state, AlertsRepository, AlertManager, FeaturesRepository, flow) {
        $scope.flow = flow;
        $scope.$on('$destroy', $rootScope.$on('visibility-changed', refreshStatus));

        var refreshStatus = function() {
            $scope.isLoading = true;
            AlertsRepository.findByFlow(flow).then(function(alerts) {
                $scope.repositoryAlert = getRepositoryAlert(alerts);

                if (null === $scope.repositoryAlert) {
                    $scope.loadingFlexCompatibility = true;
                    FeaturesRepository.findAll(flow).then(function(features) {
                        $scope.flexFeature = getFlexFeature(features);
                    }, function(error) {
                        swal("Error !", $http.getError(error) || "An unknown error occurred while loading the available features", "error");
                    })['finally'](function() {
                        $scope.loadingFlexCompatibility = false;
                    });
                }
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred while loading the status of the flow", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        var getRepositoryAlert = function(alerts) {
            for (var i = 0; i < alerts.length; i++) {
                if (['github-integration', 'bitbucket-addon'].indexOf(alerts[i].type) !== -1) {
                    return alerts[i];
                }
            }

            return null;
        };

        var getFlexFeature = function(features) {
            for (var i = 0; i < features.length; i++) {
                if (features[i].feature == 'flex') {
                    return features[i];
                }
            }

            return null;
        }

        var activateFlex = function() {
            $scope.isLoading = true;
            FeaturesRepository.enable(flow, 'flex').then(function() {
                return refreshStatus();
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred while activating flex", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.$watchGroup(['repositoryAlert', 'loadingFlexCompatibility'], function() {
            $scope.checks = [
                {
                    icon: flow.repository.type == 'bitbucket' ? 'cp-icon-bitbucket' : 'cp-icon-github',
                    title: 'Code repository access',
                    description: 'ContinuousPipe has access to your code repository',
                    status: {
                        summary: $scope.isLoading ? 'loading' : (
                            $scope.repositoryAlert === null ? 'success' : 'error'
                        ),
                        action: $scope.repositoryAlert ? {
                            'title': $scope.repositoryAlert.action.title,
                            'click': function() {
                                AlertManager.open($scope.repositoryAlert);
                            }
                        } : undefined
                    }
                },
                {
                    icon: 'touch_app',
                    title: 'ContinuousPipe for Flex',
                    description: 'You don\'t need a Kubernetes cluster, a Docker Registry, a Docker configuration, a Docker-Compose configuration... we do everything for you!',
                    status: $scope.loadingFlexCompatibility ? {'summary': 'loading'} : (
                         $scope.flexFeature ?
                            !$scope.flexFeature.available ? {
                                'summary': 'disabled',
                                'icon': 'warning',
                                'message': $scope.flexFeature.reason || 'Your application is not supported'
                            } 
                            :   (
                                $scope.flexFeature.enabled ? {'summary': 'success', 'icon': 'done'}
                                    : {
                                        'summary': 'optional',
                                        'action': {
                                            'title': 'Enable !',
                                            'click': activateFlex
                                        }
                                    }
                            )
                        : {
                            'summary': 'disabled'
                         }
                    )
                }
            ];

            $scope.isReady = $scope.checks.reduce(function(carry, check) {
                return carry && ['disabled', 'success', 'optional'].indexOf(check.status.summary) !== -1;
            }, true);
        });

        $scope.start = function() {
            $state.go('flow.dashboard', {uuid: flow.uuid});
        }

        refreshStatus();
    })
;
