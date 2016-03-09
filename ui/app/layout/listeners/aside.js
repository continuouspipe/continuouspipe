'use strict';

angular.module('continuousPipeRiver')
    .service('$aside', function ($rootScope) {
        this.set = function(enabled) {
            $rootScope.aside = enabled;
        };
    })
    .run(function($rootScope, $aside) {
        $rootScope.$on('$stateChangeSuccess', function(event, toState) {
            $aside.set(toState.aside === true);
        });
    });
