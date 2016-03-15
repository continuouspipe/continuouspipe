'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamsController', function($scope, $remoteResource, TeamRepository) {
        $remoteResource.load('teams', TeamRepository.findAll()).then(function (teams) {
            $scope.teams = teams;
        });
    })
    .controller('CreateTeamController', function($scope, $state, Slug, TeamRepository) {
        $scope.$watch('team.name', function(name, previous) {
            if ($scope.team && (!$scope.team.slug || $scope.team.slug == Slug.slugify(previous))) {
                $scope.team.slug = Slug.slugify(name);
            }
        });

        $scope.create = function(team) {
            $scope.isLoading = true;
            TeamRepository.create(team).then(function() {
                $state.go('flows', {team: team.slug});
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while create the team";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
