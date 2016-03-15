'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamUsersController', function($scope, TeamMembershipRepository, team) {
        $scope.team = team;

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
                TeamMembershipRepository.remove(team, membership.user).then(function () {
                    $scope.team.$get({slug: team.slug});
                }, function (error) {
                    var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                    swal("Error !", message, "error");
                });
            });
        };
    });
