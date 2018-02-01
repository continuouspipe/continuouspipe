'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectAlertsRepository', function($resource, $httpUtils, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/teams/:slug/alerts');

        this.findByProject = function(project) {
            return $httpUtils.oneAtATime('alerts-project-'+project.slug, function() {
                return this.resource.query({
                    slug: project.slug
                }).$promise;
            }.bind(this));
        };
    })
    .service('$httpUtils', function() {
        var runningProcesses = {};

        this.oneAtATime = function(identifier, callback) {
            if (identifier in runningProcesses) {
                return runningProcesses[identifier];
            }

            return runningProcesses[identifier] = callback().then(function(result) {
                delete runningProcesses[identifier];

                return result;
            }, function(error) {
                delete runningProcesses[identifier];

                throw error;
            });
        };
    });
