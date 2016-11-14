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
        'ngRaven',
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
        'yaru22.angular-timeago',
        'firebase',
        'RecursionHelper'
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
            var response = error || {};
            var body = response.data || {};
            var message = body.message || body.error;

            if (!message && response.status == 400) {
                // We are seeing a constraint violation list here, let's return the first one 
                message = body[0] && body[0].message;
            }

            return message;
        };
    })
;
