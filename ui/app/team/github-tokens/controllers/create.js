'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamCreateGitHubTokenController', function($scope, $state, $http,GitHubTokensRepository) {
        $scope.create = function(token) {
            $scope.isLoading = true;
            GitHubTokensRepository.create(token).then(function() {
                $state.go('github-tokens');
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while saving the GitHub token", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
