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

            // KAI-KAI view
            .state('kaikai', {
                parent: 'layout',
                url: '/kaikai/:tideUuid',
                resolve: {
                    tide: function($stateParams, TideRepository) {
                        return TideRepository.find($stateParams.tideUuid);
                    },
                    summary: function(TideSummaryRepository, tide) {
                        return TideSummaryRepository.findByTide(tide);
                    }
                },
                views: {
                    'header@layout': {
                        templateUrl: 'tide/views/kaikai/header.html',
                        controller: 'KaiKaiHeaderController'
                    },
                    '@layout': {
                        controller: 'KaiKaiController'
                    }
                },
                breadcrumb: false
            })
            .state('kaikai.logs', {
                url: '/logs',
                views: {
                    '@layout': {
                        controller: 'KaiKaiLogsController',
                        templateUrl: 'tide/views/kaikai/logs.html'
                    }
                },
                breadcrumb: false
            })
            .state('kaikai.service', {
                url: '/service/:name',
                views: {
                    '@layout': {
                        controller: 'KaiKaiServiceController',
                        templateUrl: 'tide/views/kaikai/service.html'
                    }
                },
                breadcrumb: false
            })
        ;
    });
