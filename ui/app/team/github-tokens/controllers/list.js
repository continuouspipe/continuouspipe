'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamGitHubTokensController', function($scope, $remoteResource, $http, GitHubTokensRepository) {
        var controller = this;

        this.loadTokens = function() {
            $remoteResource.load('tokens', GitHubTokensRepository.findAll()).then(function (tokens) {
                $scope.tokens = tokens;
            });
        };

        $scope.deleteToken = function(token) {
            swal({
                title: "Are you sure?",
                text: "You will not be able to cancel this action!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                GitHubTokensRepository.remove(token).then(function() {
                    swal("Deleted!", "GitHub token successfully deleted.", "success");

                    controller.loadTokens();
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occured while deleting token", "error");
                });
            });
        };

        this.loadTokens();
    });
