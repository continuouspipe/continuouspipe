'use strict';

angular.module('continuousPipeRiver')
    .service('$breadcrumbVisibility', function ($rootScope) {
        this.set = function(visible) {
            $rootScope.breadcrumbHidden = !visible;
        };
    })
    .run(function($rootScope, $breadcrumbVisibility) {
        $rootScope.$on('$stateChangeSuccess', function(event, toState) {
            $breadcrumbVisibility.set(toState.breadcrumb !== false);
        });
    });
