'use strict';

angular.module('continuousPipeRiver')
    .config(function($stateProvider) {
        $stateProvider
            .state('flows', {
                parent: 'layout',
                url: '/flows',
                templateUrl: 'flow/views/list.html',
                controller: 'FlowListController',
                ncyBreadcrumb: {
                    label: 'Flows'
                },
                aside: false
            })
        ;
    });
