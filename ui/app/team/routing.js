'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('team', {
                url: '/team/:team',
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
                        templateUrl: 'team/layout/views/aside.html'
                    },
                    'title@layout': {
                        controller: function($scope, team) {
                            $scope.team = team;
                        },
                        template: '{{ team.name || team.slug }}'
                    }
                }
            })
            .state('flows', {
                parent: 'team',
                url: '/flows',
                views: {
                    'content@': {
                        templateUrl: 'team/flows/views/list.html',
                        controller: 'FlowListController'
                    }
                },
                aside: true
            })
            .state('flows.create', {
                url: '/create',
                views: {
                    'content@': {
                        templateUrl: 'team/flows/views/create.html',
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
                parent: 'team',
                url: '/users',
                views: {
                    'content@': {
                        templateUrl: 'team/users/views/list.html',
                        controller: 'TeamUsersController'
                    }
                },
                aside: true
            })
            .state('users.add', {
                parent: 'team',
                url: '/add',
                views: {
                    'content@': {
                        templateUrl: 'team/users/views/add.html',
                        controller: 'TeamAddUserController'
                    }
                },
                aside: true
            })
            .state('clusters', {
                parent: 'team',
                url: '/clusters',
                views: {
                    'content@': {
                        templateUrl: 'team/clusters/views/list.html',
                        controller: 'TeamClustersController'
                    }
                },
                aside: true
            })
            .state('clusters.add', {
                url: '/add',
                views: {
                    'content@': {
                        templateUrl: 'team/clusters/views/add.html',
                        controller: 'TeamAddClusterController'
                    }
                },
                aside: true
            })
            .state('registry-credentials', {
                parent: 'team',
                url: '/registry-credentials',
                views: {
                    'content@': {
                        templateUrl: 'team/registry-credentials/views/list.html',
                        controller: 'TeamRegistryCredentialsController'
                    }
                },
                aside: true
            })
            .state('registry-credentials.create', {
                url: '/create',
                views: {
                    'content@': {
                        templateUrl: 'team/registry-credentials/views/create.html',
                        controller: 'TeamCreateRegistryCredentialsController'
                    }
                },
                aside: true
            })
            .state('github-tokens', {
                parent: 'team',
                url: '/github-tokens',
                views: {
                    'content@': {
                        templateUrl: 'team/github-tokens/views/list.html',
                        controller: 'TeamGitHubTokensController'
                    }
                },
                aside: true
            })
            .state('github-tokens.create', {
                url: '/create',
                views: {
                    'content@': {
                        templateUrl: 'team/github-tokens/views/create.html',
                        controller: 'TeamCreateGitHubTokenController'
                    }
                },
                aside: true
            })
            .state('configuration', {
                parent: 'team',
                url: '/configuration',
                views: {
                    'content@': {
                        templateUrl: 'team/configuration/views/edit.html',
                        controller: 'TeamConfigurationController'
                    }
                },
                aside: true
            })
        ;
    });
