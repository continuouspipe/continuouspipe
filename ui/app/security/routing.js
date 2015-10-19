'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('teams', {
                abstract: true,
                parent: 'layout',
                url: '/teams',
                ncyBreadcrumb: {
                    label: 'Teams'
                }
            })
            .state('teams.switch', {
                url: '/switch/:team',
                views: {
                    '@layout': {
                        controller: 'SwitchTeamController'
                    }
                }
            })
            .state('teams.create', {
                url: '/create',
                views: {
                    '@layout': {
                        templateUrl: 'security/views/team/create.html',
                        controller: 'CreateTeamController'
                    }
                },
                ncyBreadcrumb: {
                    label: 'Create'
                }
            })
        ;
    });
