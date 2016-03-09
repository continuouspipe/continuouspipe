'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('flow', {
                abstract: true,
                parent: 'team',
                url: '/:uuid',
                resolve: {
                    flow: function($stateParams, FlowRepository) {
                        return FlowRepository.find($stateParams.uuid);
                    }
                },
                views: {
                    'aside@': {
                        templateUrl: 'flow/views/layout/aside.html'
                    },
                    'title@': {
                        template: '<a ui-sref="flows({team: team.slug})">{{ team.slug }}</a> / {{ flow.repository.repository.name }}',
                        controller: function($scope, team, flow) {
                            $scope.team = team;
                            $scope.flow = flow;
                        }
                    }
                },
                aside: true
            })
            .state('flow.tides', {
                url: '/tides',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/tides/list.html',
                        controller: 'FlowTidesController'
                    }
                },
                aside: true
            })

            .state('flow.environments', {
                url: '/environments',
                views: {
                    'content@': {
                        templateUrl: 'flow/views/environments/list.html',
                        controller: 'FlowEnvironmentsController'
                    }
                },
                aside: true
            })
        ;
    });
