'use strict';

angular.module('continuousPipeRiver')
    .controller('PipeProviderCreateController', function($scope, $state, ProviderRepository) {
        $scope.selectProvider = function(providerType) {
            $scope.providerType = providerType;
        };

        $scope.create = function(provider) {
            ProviderRepository.create($scope.providerType, provider).then(function() {
                $state.go('providers');
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating flow";
                swal("Error !", message, "error");
            });
        };
    });
