'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamAddClusterController', function($scope, $state, $http, ClusterRepository) {
        $scope.selectType = function(type) {
            $scope.cluster = {type: type};
        };

        $scope.create = function(cluster) {
            $scope.isLoading = true;

            ClusterRepository.create(cluster).then(function() {
                $state.go('clusters');
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating cluster", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
