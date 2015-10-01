'use strict';

angular.module('continuousPipeRiver')
    .controller('TideCreateController', function($scope, $state, TideRepository, flow) {
        $scope.flow = flow;

        $scope.create = function(tide) {
            $scope.isLoading = true;
            $scope.error = null;

            TideRepository.create(flow, tide).then(function() {
                $state.go('flow.overview', {
                    uuid: flow.uuid
                });
            }, function(response) {
                $scope.error = response.data ? response.data.error : 'Unknown error ('+response.status+')';
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
