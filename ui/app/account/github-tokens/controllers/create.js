'use strict';

angular.module('continuousPipeRiver')
    .controller('GitHubTokensCreateController', function($scope, $state, GitHubTokensRepository) {
        $scope.create = function(token) {
            $scope.isLoading = true;
            GitHubTokensRepository.create(token).then(function() {
                $state.go('github-tokens');
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while saving the GitHub token";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
