'use strict';

angular.module('continuousPipeRiver')
    .service('InvitationRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/teams/:team/invitations/:uuid', {}, {
            remove: {
                method: 'DELETE'
            }
        });

        this.create = function(team, email, permissions) {
            return this.resource.save(
                {team: team.slug},
                {permissions: permissions, email: email}
            ).$promise;
        };

        this.remove = function(team, invitation) {
            return this.resource.remove({team: team.slug, uuid: invitation.uuid}).$promise;
        };
    });
