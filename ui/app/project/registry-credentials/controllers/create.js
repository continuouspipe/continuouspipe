'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectCreateRegistryCredentialsController', function($scope, $http, $state, $intercom, RegistryCredentialsRepository) {
        $scope.credentials = {
            serverAddress: 'docker.io'
        };

        $scope.create = function(credentials) {
            $scope.isLoading = true;
            RegistryCredentialsRepository.create(credentials).then(function() {
                $state.go('registry-credentials');

                $intercom.trackEvent('added-registry-credentials', {
                    registry: credentials.serverAddress,
                    username: credentials.username
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while saving the credentials", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
