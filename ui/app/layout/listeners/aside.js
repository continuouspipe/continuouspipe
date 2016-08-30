'use strict';

angular.module('continuousPipeRiver')
    .service('$aside', function ($rootScope) {
        this.set = function(enabled) {
            $rootScope.aside = enabled;
        };
    })
    .run(function($rootScope, $aside) {
        var asAsideByDefault = $(window).width() >= 960;

        $rootScope.$on('$stateChangeSuccess', function(event, toState) {
            if (asAsideByDefault) {
                $aside.set(toState.aside === true);
            }
        });
    });
