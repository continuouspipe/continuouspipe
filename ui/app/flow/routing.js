'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('flows', {
                parent: 'layout',
                url: '/flows',
                templateUrl: 'flow/views/list.html',
                controller: 'FlowListController',
                ncyBreadcrumb: {
                    label: 'Flows'
                },
                aside: false
            })
            .state('flows.create', {
                url: '/create',
                views: {
                    '@layout': {
                        templateUrl: 'flow/views/create.html',
                        controller: 'FlowCreateController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Create'
                }
            })
            .state('flow', {
                abstract: true,
                parent: 'flows',
                url: '/:uuid',
                resolve: {
                    flow: function($stateParams, FlowRepository) {
                        return FlowRepository.find($stateParams.uuid);
                    }
                },
                views: {
                    'aside@layout': {
                        templateUrl: 'flow/views/layout/aside.html'
                    }
                },
                ncyBreadcrumb: {
                    label: '#{{ flow.uuid }}'
                },
                aside: true
            })
            .state('flow.overview', {
                url: '',
                views: {
                    '@layout': {
                        templateUrl: 'flow/views/show.html',
                        controller: 'FlowController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Overview'
                }
            })
        ;
    });
