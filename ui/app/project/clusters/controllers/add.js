'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectAddClusterController', function($scope, $state, $http, $intercom, ClusterRepository, AccountRepository) {
        var clusterFromGkeCluster = function(gkeCluster) {
            return {
                type: 'kubernetes',
                identifier: gkeCluster.name,
                address: 'https://' + gkeCluster.endpoint,
                username: gkeCluster.masterAuth.username,
                password: gkeCluster.masterAuth.password,
                version: 'v' + gkeCluster.currentMasterVersion
            };
        };

        var createCluster = function(cluster) {
            if (cluster.type == 'managed') {
                return ClusterRepository.createManaged();
            }
            
            if (cluster.type == 'gke') {
                cluster = clusterFromGkeCluster(cluster.gke.cluster);
            }

            return ClusterRepository.create(cluster);
        };

        $scope.create = function(cluster) {
            $scope.isLoading = true;
            createCluster(cluster).then(function() {
                $state.go('clusters');

                $intercom.trackEvent('added-cluster', {
                    type: cluster.type
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating cluster", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.loadGoogleAccounts = function() {
            return AccountRepository.findMine().then(function(accounts) {
                $scope.googleAccounts = accounts.filter(function(account) {
                    return account.type == 'google';
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your Google accounts", "error");
            });
        };

        $scope.loadGoogleProjects = function(account) {
            return AccountRepository.findGoogleProjects(account.uuid).then(function(projects) {
                $scope.googleProjects = projects;
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your Google projects", "error");
            });
        };

        $scope.loadGoogleCluster = function(account, project) {
            return AccountRepository.findGoogleClusters(account.uuid, project.projectId).then(function(clusters) {
                $scope.googleClusters = clusters;
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your GKE clusters", "error");
            });
        };
    });
