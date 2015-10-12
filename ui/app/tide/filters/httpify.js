'use strict';

angular.module('continuousPipeRiver')
    .filter('httpify', function($sce) {
        return function(value) {
            if (typeof value != 'string') {
                return value;
            }

            if (value.substr(0, 4) != 'http') {
                value = 'http://'+value;
            }

            return $sce.trustAsResourceUrl(value);
        };
    });
