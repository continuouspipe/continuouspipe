'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectClustersController', function($scope, $remoteResource, $http, $mdDialog, $state, $intercom, ClusterRepository, project, user) {
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
                confirmButtonText: "Yes, delete it!"
            }).then(function() {
                ClusterRepository.remove(cluster).then(function() {
                    swal("Deleted!", "Cluster successfully deleted.", "success");

                    controller.loadClusters();
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting cluster", "error");
                });
            }).catch(swal.noop);
        };

        $scope.showDashboard = function(cluster) {
            $state.go('clusters.status', {identifier: cluster.identifier});
        };

        $scope.showPolicies = function(cluster) {
            $state.go('cluster.policies', {identifier: cluster.identifier});
        };

        $scope.isAdmin = user.isAdmin(project);
        
        this.loadClusters();

        $scope.inspectCluster = function(cluster) {
            var dialogScope = $scope.$new();
            dialogScope.project = project;
            dialogScope.cluster = cluster;

            $mdDialog.show({
                controller: 'ProjectClusterHealthController',
                templateUrl: 'project/clusters/views/dialogs/health.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                scope: dialogScope
            });

            $intercom.trackEvent('opened-cluster-health', {
                project: project,
                cluster: cluster
            });
        };

        $scope.clusterIsManaged = function(cluster) {
            if (!cluster.policies) {
                return false;
            }

            for (var i = 0; i < cluster.policies.length; i++) {
                if (cluster.policies[i]['name'] == 'managed') {
                    return true;
                }
            }

            return false;
        }
    });
