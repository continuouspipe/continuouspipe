'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamsController', function($scope, $remoteResource, TeamRepository) {
        $remoteResource.load('teams', TeamRepository.findAll()).then(function (teams) {
            $scope.teams = teams;
        });
    })
    .controller('CreateTeamController', function($scope, $state, $http, Slug, TeamRepository) {
        $scope.$watch('team.name', function(name, previous) {
            if ($scope.team && (!$scope.team.slug || $scope.team.slug == Slug.slugify(previous))) {
                $scope.team.slug = Slug.slugify(name);
            }
        });

        $scope.create = function(team) {
            $scope.isLoading = true;
            TeamRepository.create(team).then(function() {
                $state.go('flows', {team: team.slug});

                Intercom('trackEvent', 'created-team', {
                    team: team
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while create the team", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
