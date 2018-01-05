'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('projects', {
                url: '/',
                parent: 'layout',
                views: {
                    'content@': {
                        templateUrl: 'dashboard/views/projects/list.html',
                        controller: 'ProjectsController'
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
                        templateUrl: 'dashboard/views/projects/create.html',
                        controller: 'CreateProjectController'
                    },
                    'title@layout': {
                        template: 'Create a project'
                    }
                }
            })
        ;
    });
