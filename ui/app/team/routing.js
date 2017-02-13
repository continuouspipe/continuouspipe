'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('team', {
                url: '/team/:team',
                redirectTo: 'project'
            })
            .state('team.flows', {
                parent: 'team',
                url: '/flows',
                redirectTo: 'flows'
            })
            .state('team.flows.create', {
                url: '/create',
                redirectTo: 'flows.create'
            })
            .state('team.users', {
                parent: 'team',
                url: '/users',
                redirectTo: 'users'
            })
            .state('team.users.add', {
                parent: 'team',
                url: '/add',
                redirectTo: 'users.add'
            })
            .state('team.clusters', {
                parent: 'team',
                url: '/clusters',
                redirectTo: 'clusters'
            })
            .state('team.clusters.add', {
                url: '/add',
                redirectTo: 'clusters.add'
            })
            .state('team.registry-credentials', {
                parent: 'team',
                url: '/registry-credentials',
                redirectTo: 'registry-credentials'
            })
            .state('team.registry-credentials.create', {
                url: '/create',
                redirectTo: 'registry-credentials.create'
            })
            .state('team.configuration', {
                parent: 'team',
                url: '/configuration',
                redirectTo: 'configuration'
            })
        ;
    });
