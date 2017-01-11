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
                            '<a ui-sref="flow.dashboard({uuid: flow.uuid})">{{ flow.repository.name }}</a> / '+
                            '{{ tide.uuid }} <span class="branch"><md-icon class="cp-icon-git-branch"></md-icon> {{ tide.code_reference.branch }}</span>'
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

        $stateProvider
            .state('kaikai', {
                url: '/kaikai/:tideUuid',
                resolve: {
                    tide: function($stateParams, TideRepository) {
                        return TideRepository.find($stateParams.tideUuid);
                    }
                },
                views: {
                    'content@': {
                        controller: function($state, tide) {
                            $state.go('tide.logs', {
                                team: tide.team.slug,
                                uuid: tide.flow.uuid,
                                tideUuid: tide.uuid
                            });
                        }
                    }
                },
                aside: false
            })
        ;
    });
