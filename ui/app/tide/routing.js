'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('tide', {
                parent: 'flow',
                url: '/tide/:tideUuid',
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
                }
            })
        ;
    });
