'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('teams', {
                url: '/',
                parent: 'layout',
                views: {
                    'content@': {
                        templateUrl: 'dashboard/views/teams/list.html',
                        controller: 'TeamsController'
                    },
                    'title@layout': {
                        template: 'Teams'
                    }
                }
            })
            .state('create-team', {
                url: '/create-team',
                parent: 'layout',
                views: {
                    'content@': {
                        templateUrl: 'dashboard/views/teams/create.html',
                        controller: 'CreateTeamController'
                    },
                    'title@layout': {
                        template: 'Create a team'
                    }
                }
            })
        ;
    });
