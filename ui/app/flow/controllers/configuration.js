'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($rootScope, $scope, $remoteResource, $mdToast, $mdDialog, $state, $http, $intercom, TideRepository, EnvironmentRepository, FlowRepository, flow) {
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

                $intercom.trackEvent('updated-configuration', {
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

                    if (
                        variable.as_environment_variable && (
                            variable.as_environment_variable === true 
                            ||
                            variable.as_environment_variable.length
                        )
                    ) {
                        yamlVariable.as_environment_variable = variable.as_environment_variable;
                    }

                    return yamlVariable;
                });

            $scope.flow.yml_configuration = jsyaml.dump(parsed);
        }, true);

        $scope.addVariable = function(name) {
            $scope.variables.push({
                name: name || '',
                value: '',
                as_environment_variable: true
            });
        };

        $scope.removeVariableByKey = function(key) {
            $scope.variables.splice(key, 1);
        };

        $scope.changeExposedAsEnvironment = function(event, key) {
            var scope = $scope.$new();
            scope.variable = $scope.variables[key];

            $mdDialog.show({
                controller: 'ChangeVariableVisibilityController',
                templateUrl: 'flow/views/configuration/dialogs/variable-as-environment.html',
                parent: angular.element(document.body),
                targetEvent: event,
                clickOutsideToClose: true,
                scope: scope
            }).then(function(as_environment_variable) {
                $scope.variables[key].as_environment_variable = as_environment_variable;
            });
        };

        $scope.encryptByKey = function(key) {
            swal({
                title: "Are you sure?",
                text: "After encrypting the value, you won\'t be able to read the value again on this user interface.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, encrypt it!",
                showLoaderOnConfirm: true
            }).then(function() {
                FlowRepository.encrypt(flow, $scope.variables[key]).then(function(encryptedValue) {
                    $scope.variables[key].encrypted_value = encryptedValue;

                    delete $scope.variables[key].value;
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while encrypting the variable", "error");
                });
            }).catch(swal.noop);
        };

        $scope.delete = function() {
            swal({
                title: "Are you sure?",
                text: "This will remove the flow and its tide history",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!"
            }).then(function() {
                FlowRepository.remove(flow).then(function() {
                    swal("Deleted!", "Flow successfully deleted.", "success");

                    $state.go('flows');

                    $intercom.trackEvent('deleted-flow', {
                        flow: flow.uuid
                    });
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the flow", "error");
                });
            }).catch(swal.noop);
        };
    })
    .controller('ChangeVariableVisibilityController', function($mdDialog, $scope) {
        $scope.containerNames = [];

        if (!$scope.variable.as_environment_variable) {
            $scope.visibility = 'none';
        } else if ($scope.variable.as_environment_variable === true) {
            $scope.visibility = 'all';
        } else {
            $scope.visibility = 'names';
            $scope.containerNames = $scope.variable.as_environment_variable || [];
        }

        $scope.cancel = function() {
            $mdDialog.cancel();
        };

        $scope.change = function() {
            var answer = false;

            if ($scope.visibility == 'all') {
                answer = true;
            } else if ($scope.visibility == 'names') {
                answer = $scope.containerNames;
            }

            $mdDialog.hide(answer);
        };
    })
    .controller('FlowConfigurationChecklistController', function($scope, $rootScope, $http, $state, $q, AlertsRepository, AlertManager, FeaturesRepository, ClusterRepository, RegistryCredentialsRepository, project, flow) {
        $scope.flow = flow;

        var checks = [
            {
                icon: flow.repository.type == 'bitbucket' ? 'cp-icon-bitbucket' : 'cp-icon-github',
                title: 'Code repository access',
                description: 'ContinuousPipe has access to your code repository',
                getStatus: function() {
                    return AlertsRepository.findByFlow(flow).then(function(alerts) {
                        var getRepositoryAlert = function(alerts) {
                            for (var i = 0; i < alerts.length; i++) {
                                if (['github-integration', 'bitbucket-addon'].indexOf(alerts[i].type) !== -1) {
                                    return alerts[i];
                                }
                            }

                            return null;
                        };

                        var alert = getRepositoryAlert(alerts);

                        return {
                            summary: null === alert ? 'success' : 'error',
                            attributes: {
                                alert: alert
                            }
                        };
                    });
                },
                getAction: function(status) {
                    if (status.summary == 'error') {
                        return {
                            'title': status.attributes.alert.action.title,
                            'click': function() {
                                return AlertManager.open(status.attributes.alert);
                            }
                        }
                    }
                }
            },
            {
                icon: 'cloud',
                title: 'Managed cluster',
                description: 'You don\'t have an infrastructure or a Kubernetes cluster? No worries, we can run your containers! Click on "Enable" to register a managed cluster to your project.',
                getStatus: function() {
                    return ClusterRepository.findAll(project).then(function(clusters) {
                        var clusterIsManaged = function(cluster) {
                            if (!cluster.policies) {
                                return false;
                            }

                            for (var i = 0; i < cluster.policies.length; i++) {
                                if (cluster.policies[i].name == 'managed') {
                                    return true;
                                }
                            }

                            return false;
                        };

                        for (var i = 0; i < clusters.length; i++) {
                            if (clusterIsManaged(clusters[i])) {
                                return 'success';
                            }
                        }

                        return 'optional';
                    });
                },
                getAction: function(status) {
                    if (status.summary != 'success') {
                        return {
                            title: 'Enable',
                            click: function() {
                                return ClusterRepository.createManaged(project);
                            }
                        }
                    }
                }
            },
            {
                icon: 'storage',
                title: 'Docker image in managed registry',
                description: 'You don\'t have a Docker Registry to store your Docker images? We have you stored if you click "Enable" !',
                getStatus: function() {
                    return RegistryCredentialsRepository.findAll(project).then(function(registries) {
                        var registryIsManagedForFlow = function(registry, flow) {
                            return registry.attributes && registry.attributes.managed == true && registry.attributes.flow == flow.uuid;
                        };

                        for (var i = 0; i < registries.length; i++) {
                            if (registryIsManagedForFlow(registries[i], flow)) {
                                return 'success';
                            }
                        }

                        return 'optional';
                    });
                },
                getAction: function(status, check) {
                    if (status.summary != 'success') {
                        var visibility = check && check.last_error ? 'public' : 'private';

                        return {
                            title: visibility == 'private' ? 'Create private registry' : 'Create public registry',
                            click: function() {
                                return RegistryCredentialsRepository.createManagedForFlow(flow, visibility);
                            }
                        }
                    }
                }
            }
        ];

        $scope.$on('$destroy', $rootScope.$on('visibility-changed', function() {
            refreshStatus();
        }));

        var refreshStatus = function() {
            var loadCheck = function(check) {
                check.status = {
                    summary: 'loading'
                };

                check.$promise = check.getStatus().then(function(status) {
                    if (typeof status == 'string') {
                        status = {'summary': status};
                    }

                    var actionPromise = check.getAction(status, check);
                    if (!actionPromise || !actionPromise.then) {
                        actionPromise = $q.resolve(actionPromise);
                    }

                    actionPromise.then(function(action) {
                        if (action && action.click) {
                            var previousClick = action.click;
                            action.click = function() {
                                status.summary = 'loading';

                                var promise = previousClick();
                                if (!promise.then) {
                                    promise = $q.resolve(promise);
                                }

                                return promise.then(function(result) {
                                    check.last_result = result;
                                    $rootScope.$emit('reload-alerts');
                                }, function(error) {
                                    check.last_error = error;
                                    swal("Error !", $http.getError(error) || "An unknown error occurred while actioning the checklist item", "error");
                                })['finally'](function() {
                                    loadCheck(check);
                                });
                            };

                            status.action = action;
                        }
                    });

                    check.status = status;
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while loading the checklist item", "error");
                });

                return check;
            };

            $scope.checks = checks.map(loadCheck);
        };

        $scope.$watch('checks', function() {
            $scope.isReady = $scope.checks.reduce(function(carry, check) {
                return carry && ['disabled', 'success', 'optional'].indexOf(check.status.summary) !== -1;
            }, true);

            $scope.isLoading = $scope.checks.reduce(function(carry, check) {
                return carry || check.status.summary == 'loading';
            }, false);
        }, true);

        $scope.start = function() {
            $state.go('flow.dashboard', {uuid: flow.uuid});
        };

        refreshStatus();
    })
;
