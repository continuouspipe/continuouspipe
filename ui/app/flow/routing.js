'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('flow', {
                abstract: true,
                parent: 'project',
                url: '/:uuid',
                resolve: {
                    flow: function($stateParams, FlowRepository, $flowContext, $q) {
                        return FlowRepository.find($stateParams.uuid).then(function(flow) {
                            $flowContext.setCurrentFlow(flow);

                            return flow;
                        }, function(error) {
                            return $q.reject(error);
                        });
                    }
                },
                views: {
                    'aside@': {
                        templateUrl: 'flow/views/layout/aside.html',
                        controller: function($scope, flow) {
                            $scope.flow = flow;
                        }
                    },
                    'title@layout': {
                        template: '<a ui-sref="flows({project: project.slug})">{{ project.name || project.slug }}</a> / {{ flow.repository.name }}',
                        controller: function($scope, project, flow) {
                            $scope.project = project;
                            $scope.flow = flow;
                        }
                    },
                    'alerts@': {
                        templateUrl: 'project/views/alerts.html',
                        controller: 'FlowAlertsController'
                    }
                },
                aside: true
            })
            .state('flow.tides', {
                url: '/tides',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/tides/list.html',
                        controller: 'FlowTidesController'
                    }
                },
                aside: true
            })
            .state('flow.dashboard', {
                url: '/dashboard',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/dashboard.html',
                        controller: 'FlowDashboardController'
                    }
                },
                aside: true
            })
            .state('flow.create-tide', {
                url: '/tides/create',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/tides/create.html',
                        controller: 'FlowCreateTideController'
                    }
                },
                aside: true
            })
            .state('flow.environments', {
                url: '/environments',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/environments/list.html',
                        controller: 'FlowEnvironmentsController'
                    }
                },
                aside: true
            })
            .state('flow.environment-preview', {
                url: '/environments/:identifier',
                resolve: {
                    environment: function($stateParams, EnvironmentRepository, flow) {
                        return EnvironmentRepository.findByFlow(flow).then(function(environments) {
                            for (var key in environments) {
                                if (!environments.hasOwnProperty(key)) {
                                    continue;
                                }

                                if (environments[key].identifier === $stateParams.identifier) {
                                    return environments[key];
                                }
                            }

                            throw new Error('Environment not found');
                        });
                    }
                },
                views: {
                    'content@': {
                        templateUrl: 'flow/views/environments/show.html',
                        controller: 'EnvironmentPreviewController'
                    },
                    'header@': {
                        templateUrl: 'flow/views/environments/header.html',
                        controller: 'EnvironmentPreviewController'
                    }
                },
                aside: false
            })
            .state('flow.configuration', {
                url: '/configuration',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/configuration/edit.html',
                        controller: 'FlowConfigurationController'
                    }
                },
                aside: true
            })
            .state('flow.development-environments', {
                url: '/development-environments',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/remote/list.html',
                        controller: 'ListOfDevelopmentEnvironmentsController'
                    }
                },
                aside: true
            })
            .state('flow.create-development-environment', {
                url: '/development-environments/create',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/remote/create.html',
                        controller: 'CreateDevelopmentEnvironmentController'
                    }
                },
                params: {
                    environment: {}
                },
                aside: true
            })
            .state('flow.development-environment', {
                url: '/development-environments/:environmentUuid',
                resolve: {
                    developmentEnvironmentStatus: function($stateParams, RemoteRepository, flow) {
                        return RemoteRepository.getStatus(flow, $stateParams.environmentUuid);
                    }
                },
                views: {
                    'content@': {
                        templateUrl: 'flow/views/remote/show.html',
                        controller: 'DevelopmentEnvironmentController'
                    }
                },
                aside: true
            })
        ;
    });
