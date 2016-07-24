'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamUsersController', function($scope, TeamMembershipRepository, TeamRepository, InvitationRepository, team) {
        var load = function() {
            $scope.membersStatus = null;
            $remoteResource.load('membersStatus', TeamRepository.getMembersStatus(team.slug)).then(function (membersStatus) {
                $scope.membersStatus = membersStatus;
            });
        };

        var handleError = function(error) {
            var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
            swal("Error !", message, "error");
        };

        $scope.removeMembership = function(membership) {
            swal({
                title: "Are you sure?",
                text: "The user "+membership.user.username+" will be removed from the team.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, remove it!",
                closeOnConfirm: false
            }, function() {
                TeamMembershipRepository.remove(team, membership.user).then(load, handleError);
            });
        };

        $scope.removeInvitation = function(invitation) {
            swal({
                title: "Are you sure?",
                text: "The invitation for the user "+invitation.user_name+" will be cancelled.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, cancel it!",
                closeOnConfirm: false
            }, function() {
                InvitationRepository.remove(team, invitation).then(load, handleError);
            });
        };
    });
