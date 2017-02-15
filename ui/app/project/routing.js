'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('project', {
                url: '/project/:team',
                parent: 'layout',
                abstract: true,
                resolve: {
                    team: function($stateParams, $teamContext, $q, TeamRepository) {
                        return TeamRepository.find($stateParams.team).then(function(team) {
                            $teamContext.setCurrentTeam(team);

                            return team;
                        }, function(error) {
                            return $q.reject(error);
                        });
                    }
                },
                views: {
                    'aside@': {
                        templateUrl: 'project/layout/views/aside.html'
                    },
                    'title@layout': {
                        controller: function($scope, team) {
                            $scope.team = team;
                        },
                        template: '{{ team.name || team.slug }}'
                    },
                    'alerts@': {
                        templateUrl: 'project/views/alerts.html',
                        controller: 'ProjectAlertsController'
                    }
                }
            })
            .state('flows', {
                parent: 'project',
                url: '/flows',
                views: {
                    'content@': {
                        templateUrl: 'project/flows/views/list.html',
                        controller: 'FlowListController'
                    }
                },
                aside: true
            })
            .state('flows.create', {
                url: '/create',
                views: {
                    'content@': {
                        templateUrl: 'project/flows/views/create.html',
                        controller: 'CreateFlowController'
                    },
                    'title@layout': {
                        controller: function($scope, team) {
                            $scope.team = team;
                        },
                        template: '<a ui-sref="flows({team: team.slug})">{{ team.name || team.slug }}</a> / Create a flow'
                    }
                }
            })
            .state('users', {
                parent: 'project',
                url: '/users',
                views: {
                    'content@': {
                        templateUrl: 'project/users/views/list.html',
                        controller: 'TeamUsersController'
                    }
                },
                aside: true
            })
            .state('users.add', {
                parent: 'project',
                url: '/add',
                views: {
                    'content@': {
                        templateUrl: 'project/users/views/add.html',
                        controller: 'TeamAddUserController'
                    }
                },
                aside: true
            })
            .state('clusters', {
                parent: 'project',
                url: '/clusters',
                views: {
                    'content@': {
                        templateUrl: 'project/clusters/views/list.html',
                        controller: 'TeamClustersController'
                    }
                },
                aside: true
            })
            .state('clusters.add', {
                url: '/add',
                views: {
                    'content@': {
                        templateUrl: 'project/clusters/views/add.html',
                        controller: 'TeamAddClusterController'
                    }
                },
                aside: true
            })
            .state('registry-credentials', {
                parent: 'project',
                url: '/registry-credentials',
                views: {
                    'content@': {
                        templateUrl: 'project/registry-credentials/views/list.html',
                        controller: 'TeamRegistryCredentialsController'
                    }
                },
                aside: true
            })
            .state('registry-credentials.create', {
                url: '/create',
                views: {
                    'content@': {
                        templateUrl: 'project/registry-credentials/views/create.html',
                        controller: 'TeamCreateRegistryCredentialsController'
                    }
                },
                aside: true
            })
            .state('configuration', {
                parent: 'project',
                url: '/configuration',
                views: {
                    'content@': {
                        templateUrl: 'project/configuration/views/edit.html',
                        controller: 'TeamConfigurationController'
                    }
                },
                aside: true
            })
        ;
    });
