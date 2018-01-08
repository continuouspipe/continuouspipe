'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/teams/:slug', {}, {
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

        this.create = function(project, billingProfile) {
            return this.resource.save({team: project, billing_profile: billingProfile}).$promise;
        };

        this.delete = function(project) {
            return $resource(RIVER_API_URL+'/teams/:slug').remove({slug: project.slug}).$promise;
        };

        this.update = function(project, patch) {
            patch = $.extend(true, {}, patch);
            
            // Rewrite the "project" key to "team"
            if (patch.project) {
                patch.team = patch.project;
            }

            if (patch.billing_profile) {
                patch.billing_profile = {uuid: patch.billing_profile.uuid};
            }

            return this.resource.patch({slug: project.slug}, patch).$promise;
        }

        this.getMembersStatus = function(slug) {
            return $resource(RIVER_API_URL+'/teams/:slug/members-status').get({slug: slug}).$promise;
        };

        this.getBillingProfile = function(project) {
            return $resource(RIVER_API_URL+'/teams/:slug/billing-profile').get({slug: project.slug}).$promise.then(function(billingProfile) {
                return billingProfile;
            }, function(e) {
                Raven.captureException(e);

                return {
                    name: 'Unknown',
                    user: {
                        username: 'unknown'
                    }
                };
            });
        };
    });
