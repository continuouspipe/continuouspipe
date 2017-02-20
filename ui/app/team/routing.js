'use strict';

angular.module('continuousPipeRiver')
    .config(function ($stateProvider) {
        $stateProvider
            .state('team', {
                url: '/team/:project',
                redirectTo: 'project'
            })
            .state('team.tide-logs', {
                url: '/:uuid/:tideUuid/logs',
                redirectTo: 'tide.logs'
            })
            .state('team.flows', {
                url: '/flows',
                redirectTo: 'flows'
            })
            .state('team.flows-create', {
                url: '/create',
                redirectTo: 'flows.create'
            })
            .state('team.users', {
                url: '/users',
                redirectTo: 'users'
            })
            .state('team.users-add', {
                parent: 'team',
                url: '/add',
                redirectTo: 'users.add'
            })
            .state('team.clusters', {
                url: '/clusters',
                redirectTo: 'clusters'
            })
            .state('team.clusters-add', {
                url: '/add',
                redirectTo: 'clusters.add'
            })
            .state('team.registry-credentials', {
                url: '/registry-credentials',
                redirectTo: 'registry-credentials'
            })
            .state('team.registry-credentials-create', {
                url: '/create',
                redirectTo: 'registry-credentials.create'
            })
            .state('team.configuration', {
                url: '/configuration',
                redirectTo: 'configuration'
            })
        ;
    });
