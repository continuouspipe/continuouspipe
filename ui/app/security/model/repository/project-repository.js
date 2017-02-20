'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/teams/:slug', {}, {
            patch: {
                method: 'PATCH'
            }
        });

        this.findAll = function() {
            return this.resource.query().$promise;
        };

        this.find = function(slug) {
            return this.resource.get({slug: slug}).$promise;
        };

        this.create = function(project) {
            return this.resource.save({project: project}).$promise;
        };

        this.update = function(project, patch) {
            if (patch.billing_profile) {
                patch.billing_profile = {uuid: patch.billing_profile.uuid};
            }

            return this.resource.patch({slug: project.slug}, patch).$promise;
        }

        this.getMembersStatus = function(slug) {
            return $resource(AUTHENTICATOR_API_URL+'/api/teams/:slug/members-status').get({slug: slug}).$promise;
        };
    });
