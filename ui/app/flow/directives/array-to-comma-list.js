'use strict';

angular.module('continuousPipeRiver')
    .directive('arrayAsList', function() {
        return { 
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, element, attrs, ngModel) {
                if(!ngModel) {
                    return;
                }

                ngModel.$parsers.push(function (value) {
                    if (!value) {
                        return [];
                    }
                    
                    return value.split(',').map(function(string) {
                        return string.trim();
                    });
                });

                ngModel.$formatters.push(function (value) {
                    if (!value) {
                        return '';
                    }

                    return value.join(', ');
                });
            }
        };
    });
