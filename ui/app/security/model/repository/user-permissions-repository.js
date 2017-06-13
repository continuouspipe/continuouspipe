'use strict';

angular.module('continuousPipeRiver')
    .service('UserPermissionsRepository', function() {

        this.findForUserAndProject = function(user, project) {
            var matches = project.memberships.filter(function(member) {return member.user.username == user.username;});

            return matches.length > 0 ? matches[0].permissions : [];
        };

        this.isAdmin = function(user, project) {
            return this.findForUserAndProject(user, project).indexOf('ADMIN') > -1
        }

    });
