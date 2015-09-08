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
        'dndLists'
    ])
    .config(function($urlRouterProvider, $breadcrumbProvider, $locationProvider) {
        $urlRouterProvider.otherwise('/flows');
        $locationProvider.html5Mode(true);
        $breadcrumbProvider.setOptions({
            includeAbstract: true
        });
    });
