'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('account', {
                url: '/account',
                parent: 'layout',
                abstract: true,
                views: {
                    'content@': {
                        templateUrl: 'account/views/layout/wrapper.html'
                    }
                }
            })
            .state('connected-accounts', {
                url: '/connected-accounts',
                parent: 'account',
                views: {
                    'content@account': {
                        templateUrl: 'account/views/list.html',
                        controller: 'AccountsController'
                    },
                    'title@layout': {
                        template: 'My Account'
                    }
                }
            })
        ;
    });
