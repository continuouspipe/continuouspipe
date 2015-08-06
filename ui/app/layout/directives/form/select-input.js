'use strict';

angular.module('continuousPipeRiver')
    .directive('selectInput', function() {
        return {
            restrict: 'A',
            link: function(scope, element, attributes) {
                element.selectpicker();
            }
        };
    });
