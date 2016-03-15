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
        'angular-loading-bar',
        'ngResource',
        'ui.router',
        'ngMaterial',
        'ncy-angular-breadcrumb',
        'angular-jwt',
        'angular-md5',
        'angular-google-analytics',
        'slugifier',
        'ui.ace',
        'yaru22.angular-timeago'
    ])
    .config(function($urlRouterProvider, $breadcrumbProvider, $locationProvider, $mdThemingProvider, AnalyticsProvider) {
        $urlRouterProvider.otherwise('/');
        $locationProvider.html5Mode(true);
        $breadcrumbProvider.setOptions({
            includeAbstract: true
        });

        AnalyticsProvider
            .setAccount('UA-71216332-2')
            .setPageEvent('$stateChangeSuccess')
        ;

        $mdThemingProvider.theme('blue');
    })
    // We need to inject it at least once to have automatic tracking
    .run(function(Analytics) {})
;
