'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('layout', {
                abstract: true,
                resolve: {
                    user: function($userContext) {
                        return $userContext.refreshUser();
                    },
                    teamContext: function($teamContext) {
                        return $teamContext.refreshTeams();
                    }
                },
                views: {
                    '': {
                        templateUrl: 'layout/views/layout.html'
                    },
                    'header@layout': {
                        templateUrl: 'layout/views/header.html',
                        controller: 'HeaderController'
                    },
                    'breadcrumb@layout': {
                        templateUrl: 'layout/views/breadcrumb.html',
                        controller: 'BreadcrumbController'
                    }
                },
                ncyBreadcrumb: {
                    skip: true
                }
            })
        ;
    });
