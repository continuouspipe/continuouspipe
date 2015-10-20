'use strict';

angular.module('continuousPipeRiver')
    .controller('SwitchTeamController', function($state, $stateParams, $rootScope, $teamContext) {
        $teamContext.setCurrentSlug($stateParams.team);
        $rootScope.$emit('team-changed');
        $state.go('flows', {team: $stateParams.team});
    })
    .controller('CreateTeamController', function($scope, $state, $teamContext, TeamRepository) {
        $scope.create = function(team) {
            $scope.isLoading = true;
            TeamRepository.create(team).then(function() {
                $teamContext.refreshTeams().then(function() {
                    $state.go('teams.switch', {team: team.slug});
                });
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    })
    .controller('TeamUsersController', function($scope, TeamMembershipRepository, team) {
        $scope.team = team;

        $scope.removeMembership = function(membership) {
            $scope.isLoading = true;
            TeamMembershipRepository.remove(team, membership.user).then(function() {
                $scope.team.$get({slug: team.slug});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.addMembership = function(membership) {
            $scope.isLoading = true;
            TeamMembershipRepository.add(team, membership.user, membership.permissions).then(function() {
                $scope.team.$get({slug: team.slug});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });

