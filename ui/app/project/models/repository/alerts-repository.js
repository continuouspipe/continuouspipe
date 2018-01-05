'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectAlertsRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/teams/:slug/alerts');

        this.findByProject = function(project) {
            return this.resource.query({
                slug: project.slug
            }).$promise;
        };
    });
