'use strict';

angular.module('continuousPipeRiver')
    .service('$projectContext', function() {
        this.project = null;

        this.setCurrentProject = function(project) {
            this.project = project;
        };

        this.getCurrentProject = function() {
            return this.project;
        };
    });
