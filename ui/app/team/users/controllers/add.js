'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamAddUserController', function($scope, $state, TeamMembershipRepository, InvitationRepository, team) {
        $scope.team = team;

        var handleError = function(error) {
            var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
            return swal("Error !", message, "error");
        };

        $scope.addMembership = function(membership) {
            $scope.isLoading = true;
            TeamMembershipRepository.add(team, membership.user, membership.permissions).then(function() {
                $scope.team.$get({slug: team.slug});

                $state.go('users', {team: team.slug});
            }, function(error) {
                if (error.status != 404) {
                    return handleError(error);
                }

                swal({
                    title: "Do you want to invite this user?",
                    text: "The user '"+membership.user.username+"' is not found. Do you want us to send him an invitation email for your team?",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Yes, invite!",
                    closeOnConfirm: false
                }, function() {
                    InvitationRepository.create(team, membership.user.username, membership.permissions).then(function() {
                        swal("Invited!", "User successfully invited.", "success");

                        $state.go('users', {team: team.slug});
                    }, function(error) {
                        handleError(error);
                    });
                });
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
