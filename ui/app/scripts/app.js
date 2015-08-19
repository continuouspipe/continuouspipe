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
        'angular-jwt'
    ])
    .config(function($urlRouterProvider, $breadcrumbProvider) {
        $urlRouterProvider.otherwise('/flows');
        $breadcrumbProvider.setOptions({
            includeAbstract: true
        });
    });
