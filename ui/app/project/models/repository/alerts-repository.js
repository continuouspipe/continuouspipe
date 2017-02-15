'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectAlertsRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/teams/:uuid/alerts');

        this.findByProject = function(project) {
            return this.resource.query({
                uuid: project.uuid
            }).$promise;
        };
    });
