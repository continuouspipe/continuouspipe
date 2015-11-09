'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('registry-credentials', {
                parent: 'layout',
                url: '/registry-credentials',
                templateUrl: 'account/registry-credentials/views/list.html',
                controller: 'RegistryCredentialsListController',
                ncyBreadcrumb: {
                    label: 'Docker Registry credentials'
                },
                aside: false
            })
            .state('registry-credentials.create', {
                url: '/create',
                views: {
                    '@layout': {
                        templateUrl: 'account/registry-credentials/views/create.html',
                        controller: 'RegistryCredentialsCreateController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Create'
                }
            })
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
            .state('clusters', {
                parent: 'layout',
                url: '/clusters',
                templateUrl: 'account/clusters/views/list.html',
                controller: 'ClustersListController',
                ncyBreadcrumb: {
                    label: 'Clusters'
                },
                aside: false
            })
            .state('clusters.create', {
                url: '/create',
                views: {
                    '@layout': {
                        templateUrl: 'account/clusters/views/create.html',
                        controller: 'ClustersCreateController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Create'
                }
            })
        ;
    });
