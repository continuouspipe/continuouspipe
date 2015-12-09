'use strict';

/**
 * @ngdoc overview
 * @name kubernetesManagerUiApp
 * @description
 * # kubernetesManagerUiApp
 *
 * Main module of the application.
 */
angular
    .module('continuousPipeRiver', [
        'config',
        'ngAnimate',
        'ngMessages',
        'ngSanitize',
        'ngTouch',
        'ngResource',
        'ui.router',
        'ncy-angular-breadcrumb',
        'angular-jwt',
        'dndLists',
        'ui.ace',
        'angular-md5',
        'angular-google-analytics'
    ])
    .config(function($urlRouterProvider, $breadcrumbProvider, $locationProvider, AnalyticsProvider) {
        $urlRouterProvider.otherwise('/flows');
        $locationProvider.html5Mode(true);
        $breadcrumbProvider.setOptions({
            includeAbstract: true
        });

        AnalyticsProvider
            .setAccount('UA-71216332-2')
            .setPageEvent('$stateChangeSuccess')
        ;
    })
    // We need to inject it at least once to have automatic tracking
    .run(function(Analytics) {})
;
