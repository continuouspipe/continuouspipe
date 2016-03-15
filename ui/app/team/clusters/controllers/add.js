'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamAddClusterController', function($scope, $state, ClusterRepository) {
        $scope.selectType = function(type) {
            $scope.cluster = {type: type};
        };

        $scope.create = function(cluster) {
            $scope.isLoading = true;

            ClusterRepository.create(cluster).then(function() {
                $state.go('clusters');
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating cluster";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    });
