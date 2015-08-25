'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('providers', {
                parent: 'layout',
                url: '/providers',
                templateUrl: 'pipe/providers/views/list.html',
                controller: 'PipeProviderListController',
                ncyBreadcrumb: {
                    label: 'Cloud providers'
                },
                aside: false
            })
            .state('providers.create', {
                url: '/create',
                views: {
                    '@layout': {
                        templateUrl: 'pipe/providers/views/create.html',
                        controller: 'PipeProviderCreateController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Create'
                }
            })
        ;
    });
