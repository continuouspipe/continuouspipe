'use strict';

angular.module('continuousPipeRiver')
    .directive('remoteResource', function($remoteResource) {
        return {
            transclude: true,
            templateUrl: 'layout/views/remote-resource.html',
            scope: {
                resourceName: '@'
            },
            restrict: 'E',
            link: function(scope) {
                scope.$watch('resourceName', function(resourceName) {
                    scope.resource = $remoteResource.get(resourceName);
                });
            }
        };
    });
