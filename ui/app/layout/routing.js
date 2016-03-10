'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('layout', {
                abstract: true,
                resolve: {
                    user: function($userContext) {
                        return $userContext.refreshUser();
                    }
                },
                views: {
                    header: {
                        templateUrl: 'layout/views/header.html',
                        controller: 'HeaderController'
                    }
                }
            })
        ;
    });
