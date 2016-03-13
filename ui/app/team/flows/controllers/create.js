'use strict';

angular.module('continuousPipeRiver')
    .controller('CreateFlowController', function($scope, $state, $remoteResource, WizardRepository, RegistryCredentialsRepository, ClusterRepository, FlowRepository, team, user) {
        $scope.user = user;
        $scope.wizard = {
            step: 0,
            organisation: null,
            cluster: {
                type: 'kubernetes'
            }
        };

        // 1. Select the repository
        $remoteResource.load('organisations', WizardRepository.findOrganisations()).then(function (organisations) {
            $scope.organisations = organisations;
        });

        var loadRepositoryList = function(repositories) {
            $remoteResource.load('repositories', repositories).then(function (repositories) {
                $scope.repositories = repositories;
            });
        };

        $scope.$watch('wizard.organisation', function(organisation) {
            if (!organisation) {
                loadRepositoryList(WizardRepository.findRepositoryByCurrentUser());
            } else {
                loadRepositoryList(WizardRepository.findRepositoryByOrganisation(organisation));
            }
        });

        // 2. Docker images registry
        $scope.searchRegistries = function(text) {
            return ($scope.registryCredentials || []).filter(function(registry) {
                return registry.serverAddress.indexOf(text) !== -1;
            });
        };

        $scope.$watchGroup(['wizard.registryType', 'wizard.registry.serverAddress'], function(updatedValues, oldValues) {
            var type = updatedValues[0],
                address = updatedValues[1];

            if (!type) {
                return;
            } else if (type == 'docker-hub' && oldValues[0] != 'docker-hub') {
                address = 'docker.io';

                $scope.wizard.registry = {serverAddress: address};
            }

            var updateView = function () {
                var matchingRepositories = $scope.registryCredentials.filter(function (registry) {
                    return registry.serverAddress == address;
                });

                if ($scope.wizard.registryExists = matchingRepositories.length > 0) {
                    $scope.wizard.registry = $.extend(true, {}, matchingRepositories[0]);
                }
            };

            if (!$scope.registryCredentials) {
                $remoteResource.load('registryCredentials', RegistryCredentialsRepository.findAll()).then(function (registryCredentials) {
                    $scope.registryCredentials = registryCredentials;

                    updateView();
                });
            } else {
                updateView();
            }
        });

        // 3. Kubernetes cluster
        $scope.searchClusters = function(text) {
            return ($scope.clusters || []).filter(function(cluster) {
                return cluster.identifier.indexOf(text) !== -1;
            });
        };

        $scope.$watchGroup(['wizard.cluster.type', 'wizard.cluster.identifier'], function(updatedValues) {
            var type = updatedValues[0],
                identifier = updatedValues[1];

            if (!type) {
                return;
            }

            var updateView = function () {
                var matchingRepositories = $scope.clusters.filter(function (clusters) {
                    return clusters.identifier == identifier;
                });

                if ($scope.wizard.clusterExists = matchingRepositories.length > 0) {
                    $scope.wizard.cluster = $.extend(true, {}, matchingRepositories[0]);
                }
            };

            if (!$scope.clusters) {
                $remoteResource.load('clusters', ClusterRepository.findAll()).then(function (clusters) {
                    $scope.clusters = clusters;

                    updateView();
                });
            } else {
                updateView();
            }
        });

        // 4. Application containers
        $scope.$watchGroup(['wizard.repository', 'wizard.componentsBranch', 'wizard.step'], function(updatedValues) {
            var repository = updatedValues[0],
                branch = updatedValues[1],
                step = updatedValues[2];

            if (!repository || step != 3) {
                return;
            }

            if (!branch) {
                $scope.wizard.componentsBranch = repository.repository.default_branch;
            } else {
                $remoteResource.load('components', WizardRepository.findComponentsByRepositoryAndBranch(repository, branch)).then(function (components) {
                    $scope.components = components;
                });
            }
        });

        // 5. Generated configuration
        $scope.$watch('wizard.step', function(step) {
            if (step != 4) {
                return;
            }

            $scope.wizard.configuration = jsyaml.safeDump(createConfiguration(), {
                indent: 4
            });
        });

        // 6. Finish that sh*t
        $scope.finish = function() {
            $scope.isLoading = true;

            var promise = FlowRepository.createFromRepositoryAndTeam(team, $scope.wizard.repository).then(function(flow) {
                if ($scope.wizard.configurationStorage == 'cp') {
                    flow.yml_configuration = $scope.wizard.configuration;

                    return FlowRepository.update(flow);
                }

                return flow;
            });

            if (!$scope.wizard.registryExists) {
                promise = promise.then(function(flow) {
                    return RegistryCredentialsRepository.create($scope.wizard.registry).then(function() {
                        return flow;
                    })
                });
            }

            if (!$scope.wizard.clusterExists) {
                promise = promise.then(function(flow) {
                    return ClusterRepository.create($scope.wizard.cluster).then(function() {
                        return flow;
                    });
                });
            }

            promise.then(function(flow) {
                $state.go('flow.tides', {uuid: flow.uuid});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating the flow";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        function createConfiguration() {
            var configuration = {
                tasks: {
                    images: {
                        build: {
                            services: {}
                        }
                    },
                    deployment: {
                        deploy: {
                            cluster: $scope.wizard.cluster.identifier,
                            services: {}
                        }
                    }
                }
            };

            for (var i = 0; i < $scope.components.length; i++) {
                var component = $scope.components[i];

                if (component.has_to_be_built) {
                    configuration.tasks.images.build.services[component.name] = {
                        image: component.image_name
                    };
                }

                configuration.tasks.deployment.deploy.services[component.name] = {};
                if (component.visibility == 'public') {
                    configuration.tasks.deployment.deploy.services[component.name].specification = {
                        accessibility: {
                            from_external: true
                        }
                    };
                }

                if (component.update_policy == 'lock') {
                    configuration.tasks.deployment.deploy.services[component.name].lock = true;
                }
            }

            return configuration;
        }

    });
