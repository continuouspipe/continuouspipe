'use strict';

angular.module('continuousPipeRiver')
    .controller('SwitchTeamController', function($state, $stateParams, $rootScope, $teamContext) {
        $teamContext.setCurrentSlug($stateParams.team);
        $rootScope.$emit('team-changed');
        $state.go('flows', {team: $stateParams.team});
    })
    .controller('CreateTeamController', function($scope, $state, $teamContext, TeamRepository) {
        $scope.create = function(team) {
            TeamRepository.create(team).then(function() {
                $teamContext.refreshTeams().then(function() {
                    $state.go('teams.switch', {team: team.slug});
                });
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                swal("Error !", message, "error");
            });
        };
    });
