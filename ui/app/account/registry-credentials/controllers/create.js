'use strict';

angular.module('continuousPipeRiver')
    .controller('RegistryCredentialsCreateController', function($scope, $state, RegistryCredentialsRepository) {
        $scope.create = function(credentials) {
            RegistryCredentialsRepository.create(credentials).then(function() {
                $state.go('registry-credentials');
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while saving the credentials";
                swal("Error !", message, "error");
            });
        };
    });
