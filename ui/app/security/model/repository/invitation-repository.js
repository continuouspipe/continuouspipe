'use strict';

angular.module('continuousPipeRiver')
    .service('InvitationRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/teams/:project/invitations/:uuid', {}, {
            remove: {
                method: 'DELETE'
            }
        });

        this.create = function(project, email, permissions) {
            return this.resource.save(
                {project: project.slug},
                {permissions: permissions, email: email}
            ).$promise;
        };

        this.remove = function(project, invitation) {
            return this.resource.remove({project: project.slug, uuid: invitation.uuid}).$promise;
        };
    });
