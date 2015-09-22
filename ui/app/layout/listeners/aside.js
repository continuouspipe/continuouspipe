'use strict';

angular.module('continuousPipeRiver')
    .service('$aside', function ($rootScope) {
        this.set = function(enabled) {
            $rootScope.asideEnabled = enabled;
        };
    })
    .run(function($rootScope, $aside) {
        $rootScope.$on('$stateChangeSuccess', function(event, toState) {
            $aside.set(toState.aside !== false);
        });
    });
