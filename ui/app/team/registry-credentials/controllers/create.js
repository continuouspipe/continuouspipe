'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamCreateRegistryCredentialsController', function($scope, $state, RegistryCredentialsRepository) {
        $scope.create = function(credentials) {
            $scope.isLoading = true;
            RegistryCredentialsRepository.create(credentials).then(function() {
                $state.go('registry-credentials');
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while saving the credentials";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
