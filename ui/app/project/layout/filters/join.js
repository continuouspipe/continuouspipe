'use strict';

angular.module('continuousPipeRiver')
    .filter('join', function() {
        return function(array, separator) {
            return (array || []).join(separator);
        };
    });
