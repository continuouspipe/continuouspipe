'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('team', {
                url: '/team/:team',
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
                    aside: {
                        templateUrl: 'team/layout/views/aside.html'
                    },
                    'title': {
                        controller: function($scope, team) {
                            $scope.team = team;
                        },
                        template: '{{ team.slug }}'
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

            /*****/


            .state('github-tokens', {
                parent: 'layout',
                url: '/github-tokens',
                templateUrl: 'account/github-tokens/views/list.html',
                controller: 'GitHubTokensListController',
                ncyBreadcrumb: {
                    label: 'GitHub tokens'
                },
                aside: false
            })
            .state('github-tokens.create', {
                url: '/create',
                views: {
                    '@layout': {
                        templateUrl: 'account/github-tokens/views/create.html',
                        controller: 'GitHubTokensCreateController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Create'
                }
            })
        ;
    });
