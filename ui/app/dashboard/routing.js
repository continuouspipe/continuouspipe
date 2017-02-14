'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('projects', {
                url: '/',
                parent: 'layout',
                views: {
                    'content@': {
                        templateUrl: 'dashboard/views/teams/list.html',
                        controller: 'TeamsController'
                    },
                    'title@layout': {
                        template: 'Projects'
                    }
                }
            })
            .state('create-project', {
                url: '/create-project',
                parent: 'layout',
                views: {
                    'content@': {
                        templateUrl: 'dashboard/views/teams/create.html',
                        controller: 'CreateTeamController'
                    },
                    'title@layout': {
                        template: 'Create a project'
                    }
                }
            })
        ;
    });
