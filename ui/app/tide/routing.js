'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('tides', {
                parent: 'flow',
                url: '/tides',
                abstract: true
            })
            .state('tides.create', {
                url: '/create',
                views: {
                    '@layout': {
                        controller: 'TideCreateController',
                        templateUrl: 'tide/views/create.html'
                    }
                }
            })
            .state('tide', {
                parent: 'tides',
                url: '/:tideUuid',
                abstract: true,
                resolve: {
                    tide: function($stateParams, TideRepository) {
                        return TideRepository.find($stateParams.tideUuid);
                    }
                },
                ncyBreadcrumb: {
                    label: 'Tide #{{ tide.uuid }}'
                }
            })
            .state('tide.logs', {
                url: '/logs',
                views: {
                    '@layout': {
                        controller: 'TideLogsController',
                        templateUrl: 'tide/views/logs.html'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Logs'
                },
                aside: true
            })
        ;
    });
