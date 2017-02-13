'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('team', {
                url: '/team/:project',
                redirectTo: 'project'
            })
            .state('flows', {
                parent: 'team',
                url: '/flows',
                redirectTo: 'project.flows'
            })
            .state('flows.create', {
                url: '/create',
                redirectTo: 'project.flows.create'
            })
            .state('users', {
                parent: 'team',
                url: '/users',
                redirectTo: 'project.users'
            })
            .state('users.add', {
                parent: 'team',
                url: '/add',
                redirectTo: 'project.users.add'
            })
            .state('clusters', {
                parent: 'team',
                url: '/clusters',
                redirectTo: 'project.clusters'
            })
            .state('clusters.add', {
                url: '/add',
                redirectTo: 'project.clusters.add'
            })
            .state('registry-credentials', {
                parent: 'team',
                url: '/registry-credentials',
                redirectTo: 'project.registry-credentials'
            })
            .state('registry-credentials.create', {
                url: '/create',
                redirectTo: 'project.registry-credentials.create'
            })
            .state('configuration', {
                parent: 'team',
                url: '/configuration',
                redirectTo: 'project.configuration'
            })
        ;
    });
