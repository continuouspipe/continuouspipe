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
                parent: 'flows',
                url: '/:uuid',
                resolve: {
                    flow: function($stateParams, FlowRepository) {
                        return FlowRepository.find($stateParams.uuid);
                    }
                },
                views: {
                    '@layout': {
                        templateUrl: 'flow/views/show.html',
                        controller: 'FlowController'
                    }
                },
                ncyBreadcrumb: {
                    label: '#{{ flow.uuid }}'
                }
            })
        ;
    });
