'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('flow', {
                abstract: true,
                parent: 'team',
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
                        template: '<a ui-sref="flows({team: team.slug})">{{ team.name || team.slug }}</a> / {{ flow.repository.name }}',
                        controller: function($scope, team, flow) {
                            $scope.team = team;
                            $scope.flow = flow;
                        }
                    },
                    'alerts@': {
                        templateUrl: 'flow/views/alerts.html',
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
        ;
    });
