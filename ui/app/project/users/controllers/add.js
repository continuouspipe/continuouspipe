'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectAddUserController', function($scope, $state, $http, ProjectMembershipRepository, InvitationRepository, project) {
        $scope.project = project;

        var handleError = function(error) {
            return swal("Error !", $http.getError(error) || "An unknown error occured while create the project", "error");
        };

        $scope.addMembership = function(membership) {
            $scope.isLoading = true;
            ProjectMembershipRepository.add(project, membership.user, membership.permissions).then(function() {
                $scope.project.$get({slug: project.slug});

                $state.go('users', {project: project.slug});

                Intercom('trackEvent', 'added-user-to-project', {
                    user: membership.user.username,
                    project: project.slug
                });
            }, function(error) {
                if (error.status != 404) {
                    return handleError(error);
                }

                swal({
                    title: "Do you want to invite this user?",
                    text: "Do you want us to send '"+membership.user.username+"' an invitation email for your project?",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Yes, invite!"
                }).then(function() {
                    InvitationRepository.create(project, membership.user.username, membership.permissions).then(function() {
                        swal("Invited!", "User successfully invited.", "success");

                        $state.go('users', {project: project.slug});

                        Intercom('trackEvent', 'invited-user-to-project', {
                            email: membership.user.username,
                            project: project.slug
                        });
                    }, function(error) {
                        handleError(error);
                    });
                }).catch(swal.noop);
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
