'use strict';

angular.module('continuousPipeRiver')
    .filter('firstUpperCase', function() {
        return function(string) {
            string = string || '';

            return string.charAt(0).toUpperCase() + string.slice(1);
        };
    });
