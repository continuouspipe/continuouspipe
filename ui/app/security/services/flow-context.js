'use strict';

angular.module('continuousPipeRiver')
    .service('$flowContext', function() {
        this.flow = null;

        this.setCurrentFlow = function(flow) {
            this.flow = flow;
        };

        this.getCurrentFlow = function() {
            return this.flow;
        };
    });
