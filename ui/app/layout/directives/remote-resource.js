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
                scope.has = function(key, value) {
                    if (value === undefined) {
                        value = true;
                    }

                    return value == $remoteResource.get(scope.resourceName)[key];
                };

                scope.do = function(action) {
                    $remoteResource.get(scope.resourceName)[action]();
                };

                scope.$watch('resourceName', function(resourceName) {
                    scope.resource = $remoteResource.get(resourceName);
                });
            }
        };
    });
