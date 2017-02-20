'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('project', {
                url: '/project/:project',
                parent: 'layout',
                abstract: true,
                resolve: {
                    project: function($stateParams, $projectContext, $q, ProjectRepository) {
                        return ProjectRepository.find($stateParams.project).then(function(project) {
                            $projectContext.setCurrentProject(project);

                            return project;
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
                        controller: function($scope, project) {
                            $scope.project = project;
                        },
                        template: '{{ project.name || project.slug }}'
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
                        controller: function($scope, project) {
                            $scope.project = project;
                        },
                        template: '<a ui-sref="flows({project: project.slug})">{{ project.name || project.slug }}</a> / Create a flow'
                    }
                }
            })
            .state('users', {
                parent: 'project',
                url: '/users',
                views: {
                    'content@': {
                        templateUrl: 'project/users/views/list.html',
                        controller: 'ProjectUsersController'
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
                        controller: 'ProjectAddUserController'
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
                        controller: 'ProjectClustersController'
                    }
                },
                aside: true
            })
            .state('clusters.add', {
                url: '/add',
                views: {
                    'content@': {
                        templateUrl: 'project/clusters/views/add.html',
                        controller: 'ProjectAddClusterController'
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
                        controller: 'ProjectRegistryCredentialsController'
                    }
                },
                aside: true
            })
            .state('registry-credentials.create', {
                url: '/create',
                views: {
                    'content@': {
                        templateUrl: 'project/registry-credentials/views/create.html',
                        controller: 'ProjectCreateRegistryCredentialsController'
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
                        controller: 'ProjectConfigurationController'
                    }
                },
                aside: true
            })
        ;
    });
