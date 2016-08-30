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
    .run(function(Analytics, $rootScope, $http) {
        $rootScope.$on('user_context.user_updated', function(event, user) {
            window.Intercom("boot", {
                app_id: "i0yqsxbt",
                user_id: user.username,
                email: user.email,
                custom_launcher_selector: '#contact-us-launcher',
                hide_default_launcher: true
            });
        });

        $http.getError = function(error) {
            var body = (error || {}).data || {};

            return body.message || body.error;
        };
    })
;
