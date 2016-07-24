'use strict';

angular.module('continuousPipeRiver')
    .service('TeamMembershipRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/teams/:team/users/:username', {}, {
            add: {
                method: 'PUT'
            },
            remove: {
                method: 'DELETE'
            }
        });

        this.add = function(team, user, permissions) {
            return this.resource.add({team: team.slug, username: user.username}, {permissions: permissions}).$promise;
        };

        this.remove = function(team, user) {
            return this.resource.remove({team: team.slug, username: user.username}).$promise;
        };
    });
