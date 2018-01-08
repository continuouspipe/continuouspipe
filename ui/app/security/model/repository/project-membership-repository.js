'use strict';

angular.module('continuousPipeRiver')
    .service('ProjectMembershipRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/teams/:project/users/:username', {}, {
            add: {
                method: 'PUT'
            },
            remove: {
                method: 'DELETE'
            }
        });

        this.add = function(project, user, permissions) {
            return this.resource.add({project: project.slug, username: user.username}, {permissions: permissions}).$promise;
        };

        this.remove = function(project, user) {
            return this.resource.remove({project: project.slug, username: user.username}).$promise;
        };
    });
