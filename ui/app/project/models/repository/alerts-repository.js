'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectAlertsRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/teams/:slug/alerts');

        this.findByProject = function(project) {
            return this.resource.query({
                slug: project.slug
            }).$promise;
        };
    });
