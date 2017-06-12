'use strict';

angular.module('continuousPipeRiver')
    .service('UserPermissionsRepository', function($remoteResource, ProjectRepository) {

        this.findForUserAndProject = function(user, project) {
            return $remoteResource.load('membersStatus', ProjectRepository.getMembersStatus(project.slug)).then(function (membersStatus) {
                var matches = membersStatus.memberships.filter(function(member) {return member.user.username == user.username;});

                return matches.length > 0 ? matches[0].permissions : [];
            });
        };

    });
