'use strict';

angular.module('continuousPipeRiver')
    .config(function ($stateProvider) {
        $stateProvider
            .state('project', {
                url: '/project/:project',
                parent: 'layout',
                abstract: true,
                resolve: {
                    project: function ($stateParams, $projectContext, $q, ProjectRepository) {
                        return ProjectRepository.find($stateParams.project).then(function (project) {
                            $projectContext.setCurrentProject(project);

                            return project;
                        }, function (error) {
                            return $q.reject(error);
                        });
                    }
                },
                views: {
                    'title@layout': {
                        controller: function ($scope, project) {
                            $scope.project = project;
                        },
                        template: '{{ project.name || project.slug }}'
                    },
                    'alerts@': {
                        templateUrl: 'project/views/alerts.html',
                        controller: 'ProjectAlertsController'
                    },
                    'content@': {
                        templateUrl: 'project/layout/views/wrapper.html'
                    }
                }
            })
            .state('flows', {
                parent: 'project',
                url: '/flows',
                views: {
                    'content@project': {
                        templateUrl: 'project/flows/views/list.html',
                        controller: 'FlowListController'
                    }
                }
            })
            .state('flows.create', {
                url: '/create',
                views: {
                    'content@project': {
                        templateUrl: 'project/flows/views/create.html',
                        controller: 'CreateFlowController'
                    },
                    'title@layout': {
                        controller: function ($scope, project) {
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
                    'content@project': {
                        templateUrl: 'project/users/views/list.html',
                        controller: 'ProjectUsersController'
                    }
                }
            })
            .state('users.add', {
                parent: 'project',
                url: '/add',
                views: {
                    'content@project': {
                        templateUrl: 'project/users/views/add.html',
                        controller: 'ProjectAddUserController'
                    }
                }
            })
            .state('clusters', {
                parent: 'project',
                url: '/clusters',
                views: {
                    'content@project': {
                        templateUrl: 'project/clusters/views/list.html',
                        controller: 'ProjectClustersController'
                    }
                }
            })
            .state('clusters.add', {
                url: '/add',
                views: {
                    'content@project': {
                        templateUrl: 'project/clusters/views/add.html',
                        controller: 'ProjectAddClusterController'
                    }
                }
            })
            .state('cluster', {
                parent: 'clusters',
                url: '/:identifier',
                resolve: {
                    cluster: function(ClusterRepository, $stateParams, project) {
                        return ClusterRepository.find($stateParams.identifier, project);
                    }
                }
            })
            .state('cluster.policies', {
                url: '/policies',
                views: {
                    'content@project': {
                        templateUrl: 'project/clusters/views/policies.html',
                        controller: 'ClusterPoliciesController'
                    }
                }
            })
            .state('clusters.status', {
                url: '/:identifier/status',
                resolve: {
                    cluster: function(ClusterRepository, $stateParams, project) {
                        return ClusterRepository.find($stateParams.identifier).then(function(cluster) {
                            // Compatibility with kube-status and CP's setup
                            cluster.identifier = project.slug + '+' + cluster.identifier;

                            return cluster;
                        });
                    }
                },
                views: {
                    'content@project': {
                        templateUrl: 'bower_components/kube-status/ui/app/dashboard/views/status/layout.html',
                        controller: 'ClusterStatusLayoutController'
                    }
                }
            })
            .state('cluster-status-view', {
                parent: 'clusters.status',
                url: '/{status}',
                views: {
                    status: {
                        templateUrl: 'bower_components/kube-status/ui/app/dashboard/views/status/full.html',
                        controller: 'ClusterStatusController'                    
                    }
                }
            })
            .state('registry-credentials', {
                parent: 'project',
                url: '/registry-credentials',
                views: {
                    'content@project': {
                        templateUrl: 'project/registry-credentials/views/list.html',
                        controller: 'ProjectRegistryCredentialsController'
                    }
                }
            })
            .state('registry-credentials.create', {
                url: '/create',
                views: {
                    'content@project': {
                        templateUrl: 'project/registry-credentials/views/create.html',
                        controller: 'ProjectCreateRegistryCredentialsController'
                    }
                }
            })
            .state('configuration', {
                parent: 'project',
                url: '/configuration',
                views: {
                    'content@project': {
                        templateUrl: 'project/configuration/views/edit.html',
                        controller: 'ProjectConfigurationController'
                    }
                }
            })
        ;
    });
