'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectCreateRegistryCredentialsController', function($scope, $state, RegistryCredentialsRepository) {
        $scope.credentials = {
            serverAddress: 'docker.io'
        };

        $scope.create = function(credentials) {
            $scope.isLoading = true;
            RegistryCredentialsRepository.create(credentials).then(function() {
                $state.go('registry-credentials');

                Intercom('trackEvent', 'added-registry-credentials', {
                    registry: credentials.serverAddress,
                    username: credentials.username
                });
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while saving the credentials";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
