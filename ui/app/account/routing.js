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
        ;
    });
