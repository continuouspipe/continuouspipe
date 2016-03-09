'use strict';

angular.module('continuousPipeRiver')
    .controller('TeamClustersController', function($scope, $remoteResource, ClusterRepository, team) {
        var controller = this;

        this.loadClusters = function() {
            $remoteResource.load('clusters', ClusterRepository.findAll()).then(function (clusters) {
                $scope.clusters = clusters;
            });
        };

        $scope.deleteCluster = function(cluster) {
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this cluster!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                ClusterRepository.remove(cluster).then(function() {
                    swal("Deleted!", "Cluster successfully deleted.", "success");

                    controller.loadClusters();
                }, function() {
                    swal("Error !", "An unknown error occurred while deleting cluster", "error");
                });
            });
        };

        this.loadClusters();
    });
