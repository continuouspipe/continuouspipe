'use strict';

angular.module('continuousPipeRiver')
    .controller('HeaderController', function($scope, $rootScope, user, $teamContext, AUTHENTICATOR_API_URL) {
        $scope.logoutUrl = AUTHENTICATOR_API_URL+'/logout';
        $scope.user = user;

        var reloadTeamContext = function() {
            $scope.teams = $teamContext.getAll();
            $scope.currentTeam = $teamContext.getCurrent();
        };

        reloadTeamContext();
        $rootScope.$on('team-changed', function() {
            reloadTeamContext();
        });
    });
