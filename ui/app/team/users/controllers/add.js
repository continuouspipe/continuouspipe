'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamAddUserController', function($scope, $state, TeamMembershipRepository, team) {
        $scope.team = team;

        $scope.addMembership = function(membership) {
            $scope.isLoading = true;
            TeamMembershipRepository.add(team, membership.user, membership.permissions).then(function() {
                $scope.team.$get({slug: team.slug});

                $state.go('users', {team: team.slug});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
