'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowTidesController', function($scope, $remoteResource, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        $remoteResource.load('tides', TideRepository.findByFlow(flow)).then(function (tides) {
            $scope.tides = tides;
        });
    })
    .controller('FlowCreateTideController', function($scope, $state, $http, TideRepository, flow) {
        $scope.flow = flow;

        $scope.create = function(tide) {
            $scope.isLoading = true;
            $scope.error = null;

            TideRepository.create(flow, tide).then(function() {
                $state.go('flow.tides', {
                    uuid: flow.uuid
                });

                Intercom('trackEvent', 'created-tide', {
                    flow: flow.uuid,
                    tide: tide
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating a tide", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
