'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('tide', {
                parent: 'flow',
                url: '/:tideUuid',
                abstract: true,
                resolve: {
                    tide: function($stateParams, TideRepository) {
                        return TideRepository.find($stateParams.tideUuid);
                    }
                },
                views: {
                    'title@layout': {
                        template:
                            '<a ui-sref="flows({team: team.slug})">{{ team.name || team.slug }}</a> / '+
                            '<a ui-sref="flow.tides({uuid: flow.uuid})">{{ flow.repository.repository.name }}</a> / '+
                            '{{ tide.uuid }}'
                        ,
                        controller: function($scope, team, flow, tide) {
                            $scope.team = team;
                            $scope.flow = flow;
                            $scope.tide = tide;
                        }
                    }
                }
            })
            .state('tide.logs', {
                url: '/logs',
                views: {
                    'content@': {
                        controller: 'TideLogsController',
                        templateUrl: 'tide/views/logs.html'
                    }
                },
                resolve: {
                    summary: function(TideSummaryRepository, tide) {
                        return TideSummaryRepository.findByTide(tide);
                    }
                },
                aside: false
            })
        ;
    });
