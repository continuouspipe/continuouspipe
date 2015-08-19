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
                scope.hasStatus = function(status) {
                    return status === $remoteResource.get(scope.resourceName).status;
                };

                scope.$watch('resourceName', function(resourceName) {
                    scope.resource = $remoteResource.get(resourceName);
                });
            }
        };
    });
