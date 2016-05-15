'use strict';

angular
    .module('logstream', [
        'ngAnimate',
        'ngResource',
        'ngRoute',
        'ngSanitize',
        'ngTouch',
        'firebase',
        'RecursionHelper',
        'config'
    ])
    .config(function ($routeProvider) {
        $routeProvider
            .when('/log/:identifier', {
                templateUrl: 'views/logs.html',
                controller: 'LogsCtrl',
                controllerAs: 'logs'
            })
            .when('/', {
                templateUrl: 'views/about.html'
            })
            .otherwise({
                redirectTo: '/'
            });
    });
