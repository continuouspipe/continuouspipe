'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('teams', {
                url: '/',
                views: {
                    content: {
                        templateUrl: 'dashboard/views/teams/list.html',
                        controller: 'TeamsController'
                    },
                    title: {
                        template: 'Teams'
                    }
                }
            })
            .state('create-team', {
                url: '/create-team',
                views: {
                    content: {
                        templateUrl: 'dashboard/views/teams/create.html',
                        controller: 'CreateTeamController'
                    },
                    title: {
                        template: 'Create a team'
                    }
                }
            })
        ;
    });
