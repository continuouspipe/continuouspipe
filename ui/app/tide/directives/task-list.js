'use strict';

angular.module('continuousPipeRiver')
    .directive('taskList', function() {
        return {
            scope: {
                tide: '='
            },
            templateUrl: 'tide/views/task-list.html',
            restrict: 'E'
        };
    });
