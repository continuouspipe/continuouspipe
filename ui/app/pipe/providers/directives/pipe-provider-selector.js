'use strict';

angular.module('continuousPipeRiver')
    .directive('pipeProviderSelector', function() {
        return {
            restrict: 'E',
            scope: {
                context: '='
            },
            templateUrl: 'pipe/providers/views/directives/pipe-provider-selector.html',
            controller: function($scope, $remoteResource, ProviderRepository) {
                $remoteResource.load('providers', ProviderRepository.findAll()).then(function (providers) {
                    $scope.providers = providers.map(function(provider) {
                        return {
                            name: '('+provider.type+') '+provider.identifier,
                            identifier: provider.type+'/'+provider.identifier
                        };
                    });
                });
            }
        };
    });
