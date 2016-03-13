'use strict';

angular.module('continuousPipeRiver')
    .filter('tideStatusClass', function() {
        return function(status) {
            return 'status-'+status;
        };
    });
