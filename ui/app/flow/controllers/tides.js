'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowTidesController', function($scope, $remoteResource, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        /**
        $remoteResource.load('tides', $authenticatedFirebaseDatabase.get(flow).then(function(database) {
            $scope.pipelines = $firebaseArray(
                database.ref().child('flows/'+flow.uuid+'/pipelines')
            );

            return $scope.pipelines.$loaded().then(function() {

            });
        });
        **/

        $remoteResource.load('tides', TideRepository.findByFlow(flow)).then(function (tides) {
            $scope.tides = tides;
        });
    })
    .controller('FlowCreateTideController', function($scope, $state, $http, $intercom, TideRepository, flow) {
        $scope.flow = flow;

        $scope.branches = [];
        $scope.searchText;

        TideRepository.findBranches(flow).then(function(branches) {
            angular.forEach(branches, function(value, key) {
                $scope.branches.push(value.name);
            });
        }, function(error) {
            console.error($http.getError(error) || "An unknown error occured while retrieving branches");
        });

        $scope.create = function(tide) {
            tide.branch = tide.branch ? tide.branch : $scope.searchText;

            $scope.isLoading = true;
            $scope.error = null;

            TideRepository.create(flow, tide).then(function() {
                $state.go('flow.dashboard', {
                    uuid: flow.uuid
                });

                $intercom.trackEvent('created-tide', {
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
