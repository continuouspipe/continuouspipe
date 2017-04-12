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
        'yaru22.angular-timeago',
        'firebase',
        'RecursionHelper',
        'angularResizable'
    ])
    .config(function ($urlRouterProvider, $breadcrumbProvider, $locationProvider, $mdThemingProvider, AnalyticsProvider) {
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

        firebase.initializeApp({
            apiKey: "AIzaSyDIK_08syPHkRxcf2n8zJ48XAVPHWpTsp0",
            authDomain: "continuous-pipe.firebaseapp.com",
            databaseURL: "https://continuous-pipe.firebaseio.com",
        });
    })
    .factory('$exceptionHandler', function ($window, $log, SENTRY_DSN) {
        if (SENTRY_DSN) {
            Raven.config(SENTRY_DSN).install();
        }

        return function (exception, cause) {
            $log.error.apply($log, arguments);

            if (SENTRY_DSN) {
                Raven.captureException(exception);
            }
        };
    })
    // We need to inject it at least once to have automatic tracking
    .run(['$rootScope', '$state', '$http', function ($rootScope, $state, $http) {
        function capitalizeFirstLetter(word) {
            return word.charAt(0).toUpperCase() + word.slice(1);
        }

        function titleCase(text) {
            return text.replace(/[\.\-\_]/g, ' ').split(' ').map(capitalizeFirstLetter).join(' ');
        }

        function formatTitle(text) {
            var titleCasedText = titleCase(text);
            return titleCasedText ? titleCasedText + ' - ' : '';
        }

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) $rootScope.$emit('visibility-changed');
        });

        $rootScope.$on('$stateChangeStart', function (event, current, params) {
            $rootScope.title = formatTitle(current.name);

            if (current.redirectTo) {
                event.preventDefault();
                $state.go(current.redirectTo, params, { location: 'replace' });
            }
        });

        $rootScope.$on('user_context.user_updated', function (event, user) {
            window.Intercom("boot", {
                app_id: "i0yqsxbt",
                user_id: user.username,
                email: user.email,
                custom_launcher_selector: '#contact-us-launcher',
                hide_default_launcher: true
            });

            window.satismeter({
                writeKey: "MAY39UHqizidGBSa",
                userId: user.username,
                traits: {
                    email: user.email
                }
            });
        });

        $http.getError = function (error) {
            var response = error || {};
            var body = response.data || {};
            var message = body.message || body.error;

            if (!message && response.status == 400) {
                // We are seeing a constraint violation list here, let's return the first one
                message = body[0] && body[0].message;
            }

            if (typeof message == 'object') {
                message = message.message;
            }

            return message;
        };
    }])
    ;
